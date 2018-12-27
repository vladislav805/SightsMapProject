<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\Event;
	use Model\IController;
	use Model\Params;
	use Model\Photo;
	use Model\Sight;
	use function Method\Event\sendEvent;

	/**
	 * Изменение прикрепленных к месту фотографий
	 * @package Method\Point
	 */
	class SetPhotos extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

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
			if (!$this->sightId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId is not specified");
			}

			/** @var Sight $point */
			$point = $main->perform(new GetById((new Params())->set("sightId", $this->sightId)));

			assertOwner($main, $point, ErrorCode::ACCESS_DENIED);

			$main->makeRequest("DELETE FROM `pointPhoto` WHERE `pointId` = ?")->execute([$this->sightId]);

			if (sizeOf($this->photoIds)) {
				$sql = <<<SQL
INSERT INTO
	`pointPhoto` (`pointId`, `photoId`)
SELECT
	:pointId AS `pointId`,
	`photoId`
FROM
	`photo`
WHERE
	`photoId` = :photoId AND `type` = :photoType
SQL;

				$success = 0;
				$all = sizeOf($this->photoIds);

				foreach ($this->photoIds as $photoId) {
					$stmt = $main->makeRequest($sql);
					$stmt->execute([":pointId" => $this->sightId, ":photoType" => Photo::TYPE_SIGHT, ":photoId" => $photoId]);
					$success += $stmt->rowCount();
				}

				if ($point->getOwnerId() != $main->getSession()->getUserId() && $success) {
					sendEvent($main, $point->getOwnerId(), Event::EVENT_PHOTO_ADDED, $point->getId());
				}

				return ($success === $all);
			}

			return true;
		}
	}