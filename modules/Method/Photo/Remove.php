<?

	namespace Method\Photo;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\PhotoController;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $photoId;

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			try {
				$ctl = new PhotoController($main);

				$photo = $ctl->getById($this->photoId);

				return $ctl->remove($photo);
			} catch (InvalidArgumentException $e) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND);
			}

			// TODO: send event on remove and reference to place
			/*if ($photo->getOwnerId() != $main->getSession()->getUserId()) {
				sendEvent($main, $photo->getOwnerId(), Event::EVENT_PHOTO_REMOVED, $photo->getId());
			}*/
		}
	}