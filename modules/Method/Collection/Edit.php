<?

	namespace Method\Collection;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\Collection;
	use Model\IController;
	use ObjectController\CollectionController;

	class Edit extends APIPrivateMethod {

		/** @var int */
		protected $collectionId;

		/** @var string */
		protected $title;

		/** @var string */
		protected $description = "";

		/** @var string */
		protected $type;

		/** @var int|null */
		protected $cityId;

		/**
		 * @param IController $main
		 * @return Collection
		 */
		public function resolve(IController $main) {
			if (!inRange(mb_strlen($this->title), 2, 256)) {
				throw new APIException(ErrorCode::NO_PARAM, null, "title so short or so long");
			}

			if (!in_array($this->type, [Collection::TYPE_PRIVATE, Collection::TYPE_PUBLIC])) {
				throw new APIException(ErrorCode::INVALID_TYPE_COLLECTION, null, "type is invalid. Supported values: PUBLIC or PRIVATE");
			}

			$ctl = new CollectionController($main);

			$collection = $ctl->getById($this->collectionId);

			if (!$collection) {
				throw new APIException(ErrorCode::COLLECTION_NOT_FOUND);
			}

			$collection->setTitle($this->title)
				->setDescription($this->description)
				->setType($this->type)
				->setCityId($this->cityId);

			return $ctl->edit($collection);
		}
	}