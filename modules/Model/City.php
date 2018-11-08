<?

	namespace Model;

	class City implements IItem {

		/** @var int */
		protected $cityId;

		/** @var string */
		protected $name;

		/** @var int|null */
		protected $parentId;

		/** @var City[]|null */
		protected $children = null;


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
		 * @param City $city
		 */
		public function addChild(City $city) {
			if (!$this->children) {
				$this->children = [];
			}
			$this->children[] = $city;
		}

		/**
		 * @return City[]|null
		 */
		public function getChildren() {
			return $this->children;
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