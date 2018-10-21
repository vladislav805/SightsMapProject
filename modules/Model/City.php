<?

	namespace Model;

	class City implements IItem {

		/** @var int */
		private $cityId;

		/** @var string */
		private $name;

		/** @var int|null */
		private $parentId;


		public function __construct($d) {
			$this->cityId = (int) $d["cityId"];
			$this->name = $d["name"];
			$this->parentId = (int) $d["parentId"];
		}

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId() {
			return $this->cityId;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * @return int|null
		 */
		public function getParentId() {
			return $this->parentId;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"cityId" => $this->cityId,
				"name" => $this->name,
				"parentId" => $this->parentId > 0 ? $this->parentId : null
			];
		}
	}