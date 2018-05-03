<?

	namespace Method\Photo;

	use Model\IController;
	use Model\Photo;
	use Method\APIException;
	use Method\APIPublicMethod;
	use PDO;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $photoId;

		/**
		 * GetById constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

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
				throw new APIException(ERROR_PHOTO_NOT_FOUND);
			}

			return new Photo($data);
		}
	}