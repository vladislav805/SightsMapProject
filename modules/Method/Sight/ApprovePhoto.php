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
	 * Подтверждение фотографии автором достопримечательности или администратором
	 * @package Method\Sight
	 */
	class ApprovePhoto extends APIPrivateMethod {

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

			$currentUserId = $main->getUser()->getId();

			if ($sight->getOwnerId() !== $currentUserId && !isTrustedUser($main->getUser())) {
				throw new APIException(ErrorCode::ACCESS_DENIED);
			}

			$sql = <<<SQL
UPDATE
    `photo`,
    `sightPhoto`
SET
    `photo`.`type` = :photoType
WHERE
      `photo`.`photoId` = `sightPhoto`.`photoId` AND
      `photo`.`photoId` = :photoId AND 
      `sightPhoto`.`sightId` = :sightId
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":sightId" => $sight->getId(),
				":photoType" => Photo::TYPE_SIGHT,
				":photoId" => $photo->getId()
			]);

			return $stmt->rowCount() > 0;

		}
	}