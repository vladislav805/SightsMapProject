<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\IController;
	use Model\Params;
	use Model\Photo;
	use Model\Point;

	/**
	 * Изменение прикрепленных к месту фотографий
	 * @package Method\Point
	 */
	class SetPhotos extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int[] */
		protected $photoIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->photoIds = array_map("intval", explode(",", $this->photoIds));
		}

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId is not specified");
			}

			/** @var Point $point */
			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			assertOwner($main, $point->getOwnerId(), ErrorCode::ACCESS_DENIED);

			$main->makeRequest("DELETE FROM `pointPhoto` WHERE `pointId` = ?")->execute([$this->pointId]);

			if (sizeOf($this->photoIds)) {
				$photoIds = join(",", $this->photoIds);
				$sql = <<<SQL
INSERT INTO
	`pointPhoto` (`pointId`, `photoId`)
SELECT
	:pointId AS `pointId`,
	`photoId`
FROM
	`photo`
WHERE
	`photoId` IN ($photoIds) AND `type` = :photoType
SQL;
				$stmt = $main->makeRequest($sql);
				$stmt->execute([":pointId" => $this->pointId, ":photoType" => Photo::TYPE_POINT]);
				$count = $stmt->rowCount();

				return [$sql, $count];

				if ($point->getOwnerId() != $main->getSession()->getUserId() && $count) {
					sendEvent($main, $point->getOwnerId(), Event::EVENT_PHOTO_ADDED, $point->getId());
				}


			}

			return true;
		}
	}