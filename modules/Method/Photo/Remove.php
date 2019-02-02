<?

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;
	use Model\Photo;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $photoId;

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			/** @var Photo $photo */
			$photo = $main->perform(new GetById((new Params)->set("photoId", $this->photoId)));

			if (!$photo) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND);
			}

			assertOwner($main, $photo, ErrorCode::ACCESS_DENIED);

			$stmt = $main->makeRequest("DELETE FROM `photo` WHERE `photoId` = :photoId LIMIT 1");
			$stmt->execute([":photoId" => $this->photoId]);

			if (!$stmt->rowCount()) {
				return false;
			}

			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameThumbnail());
			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameOriginal());

			// TODO: send event on remove and reference to place
			/*if ($photo->getOwnerId() != $main->getSession()->getUserId()) {
				sendEvent($main, $photo->getOwnerId(), Event::EVENT_PHOTO_REMOVED, $photo->getId());
			}*/

			return true;
		}
	}