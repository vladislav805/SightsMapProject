<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use Model\Sight;

	/**
	 * Изменение прикрепленных к месту фотографий
	 * @package Method\Sight
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

			/** @var Sight $sight */
			$sight = $main->perform(new GetById(["sightId" => $this->sightId]));

			assertOwner($main, $sight, ErrorCode::ACCESS_DENIED);

			if (sizeOf($this->photoIds)) {
				$sql = <<<SQL
INSERT INTO
	`sightPhoto` (`sightId`, `photoId`, `orderId`)
SELECT
	:sightId AS `sightId`,
	`photoId`,
    :orderId AS `orderId`
FROM
	`photo`
WHERE
	`photoId` = :photoId AND `type` = :photoType
ON DUPLICATE KEY UPDATE `sightPhoto`.`photoId` = :photoId, `sightPhoto`.`sightId` = :sightId, `orderId` = :orderId
SQL;

				$success = 0;
				$all = sizeOf($this->photoIds);

				foreach ($this->photoIds as $i => $photoId) {
					$stmt = $main->makeRequest($sql);
					$stmt->execute([":sightId" => $this->sightId, ":photoType" => Photo::TYPE_SIGHT, ":photoId" => $photoId, ":orderId" => $i]);
					$success += $stmt->rowCount();
				}

				return $success === $all;
			}

			return true;
		}
	}