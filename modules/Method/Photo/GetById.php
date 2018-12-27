<?

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use PDO;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $photoId;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = $main->makeRequest("SELECT * FROM `photo` WHERE `photoId` = ?");
			$sql->execute([$this->photoId]);
			$data = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$data) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND, null, "Photo not found");
			}

			return new Photo($data);
		}
	}