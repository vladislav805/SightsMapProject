<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Sight;
	use ObjectController\PhotoController;

	/**
	 * Удаление автором достопримечательности или администратором предложенной фотографии
	 * @package Method\Sight
	 */
	class DeclinePhoto extends APIPrivateMethod {

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

			$pc = new PhotoController($main);
			$photo = $pc->getById($this->photoId);

			$currentUserId = $main->getUser()->getId();

			if ($sight->getOwnerId() !== $currentUserId && !isTrustedUser($main->getUser())) {
				throw new APIException(ErrorCode::ACCESS_DENIED);
			}

			$result = $pc->remove($photo);

			return $result;

		}
	}