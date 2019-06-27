<?

	namespace Method\Collection;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\Collection;
	use Model\IController;
	use ObjectController\CollectionController;

	class Add extends APIPrivateMethod {

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
		 * @return mixed
		 */
		public function resolve(IController $main) {
			if (!inRange(mb_strlen($this->title), 2, 256)) {
				throw new APIException(ErrorCode::NO_PARAM, null, "title so short or so long");
			}

			if (!in_array($this->type, [Collection::TYPE_PRIVATE, Collection::TYPE_PUBLIC])) {
				throw new APIException(ErrorCode::INVALID_TYPE_COLLECTION, null, "type is invalid. Supported values: PUBLIC or PRIVATE");
			}

			$collection = new Collection([
				"ownerId" => $main->getUser()->getId(),
				"title" => $this->title,
				"description" => $this->description,
				"dateCreated" => time(),
				"type" => $this->type,
				"cityId" => $this->cityId
			]);

			return (new CollectionController($main))->add($collection);
		}
	}