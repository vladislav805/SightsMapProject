<?

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use ObjectController\PhotoController;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $photoId;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			return (new PhotoController($main))->getById($this->photoId);
		}
	}