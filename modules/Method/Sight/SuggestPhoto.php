<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use Model\Sight;
	use ObjectController\PhotoController;

	/**
	 * Добавление фотографии от пользователя не автора с последующей проверкой автора или администратором
	 * @package Method\Sight
	 */
	class SuggestPhoto extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $photoId;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId || !$this->photoId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId or photoId is not specified");
			}

			/** @var Sight $sight */
			$sight = $main->perform(new GetById(["sightId" => $this->sightId]));
			$photo = (new PhotoController($main))->getById($this->photoId);

			if ($sight->getOwnerId() === $photo->getOwnerId()) {
				throw new APIException(ErrorCode::INVALID_METHOD_USING_ADD_PHOTOS);
			}

			$sql = <<<SQL
INSERT INTO `sightPhoto` (`sightId`, `photoId`) SELECT
	:sightId AS `sightId`,
	`photoId`
FROM
	`photo`
WHERE
	`photoId` = :photoId AND `type` = :photoType
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":sightId" => $sight->getId(),
				":photoType" => Photo::TYPE_SIGHT_SUGGESTED,
				":photoId" => $photo->getId()
			]);

			return $stmt->rowCount() > 0;

		}
	}