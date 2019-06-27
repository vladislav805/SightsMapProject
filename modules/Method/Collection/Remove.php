<?

	namespace Method\Collection;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\CollectionController;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $collectionId;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			if (!$this->collectionId) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$ctl = new CollectionController($main);

			$collection = $ctl->getById($this->collectionId);

			var_dump($collection);

			if (!$collection) {
				throw new APIException(ErrorCode::COLLECTION_NOT_FOUND, null, "Collection not found");
			}

			return $ctl->remove($collection);
		}
	}