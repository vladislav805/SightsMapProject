<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Method\Photo\GetById;
	use Method\Photo\Remove;
	use Model\IController;
	use Model\Photo;

	/**
	 * @package Method\Account
	 */
	class SetProfilePhoto extends APIPrivateMethod {

		/** @var int[] */
		protected $photoId;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->photoId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "photoId is not specified");
			}

			$oldPhoto = $main->getUser()->getPhoto();
			$userId = $main->getUser()->getId();

			/** @var Photo $photo */
			$photo = $main->perform(new GetById(["photoId" => $this->photoId]));

			if (!$photo) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND, null, "Specified photo not found");
			}

			assertOwner($main, $photo, ErrorCode::ACCESS_DENIED);

			if ($photo->getType() !== Photo::TYPE_PROFILE) {
				throw new APIException(ErrorCode::INVALID_PHOTO_TYPE, null, "Invalid photo type (needed Photo::TYPE_PROFILE)");
			}

			$stmt = $main->makeRequest("UPDATE `user` SET `photoId` = :pid WHERE `userId` = :uid");
			$success = $stmt->execute([":pid" => $this->photoId, ":uid" => $userId]);

			if ($oldPhoto->getType() !== Photo::TYPE_EMPTY) {
				$main->perform(new Remove(["photoId" => $oldPhoto->getId()]));
			}

			return $success;
		}
	}