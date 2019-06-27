<?

	namespace Method\Collection;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use ObjectController\CollectionController;

	class Get extends APIPublicMethod {

		/** @var int */
		protected $ownerId;

		/** @var int */
		protected $cityId;

		/** @var int */
		protected $offset = 0;

		/** @var int */
		protected $count = 50;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			$args = [];

			if ($this->ownerId) {
				$args["ownerId"] = $this->ownerId;
			}

			if ($this->cityId) {
				$args["cityId"] = $this->cityId;
			}

			return (new CollectionController($main))->get(null, $this->count, $this->offset, $args);
		}
	}