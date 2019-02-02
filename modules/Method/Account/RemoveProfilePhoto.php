<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Method\Photo\Remove;
	use Model\IController;
	use Model\Photo;

	/**
	 * @package Method\Account
	 */
	class RemoveProfilePhoto extends APIPrivateMethod {

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$oldPhoto = $main->getUser()->getPhoto();

			if ($oldPhoto->getType() === Photo::TYPE_EMPTY) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND);
			}

			return $main->perform(new Remove(["photoId" => $oldPhoto->getId()]));
		}
	}