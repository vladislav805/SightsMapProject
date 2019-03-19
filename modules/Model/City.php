<?

	namespace Model;

	class City implements IItem {

		use APIModelGetterFields;

		/** @var int */
		protected $cityId;

		/** @var string */
		protected $name;

		/** @var int|null */
		protected $parentId;

		/** @var City[]|null */
		protected $children = null;

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;


		public function __construct($d) {
			isset($d["cityId"]) && ($this->cityId = (int) $d["cityId"]);
			$this->name = $d["name"];
			isset($d["parentId"]) && ($this->parentId = (int) $d["parentId"]);
			isset($d["lat"]) && ($this->lat = (double) $d["lat"]);
			isset($d["lng"]) && ($this->lng = (double) $d["lng"]);
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
		 * @return float
		 */
		public function getLat() {
			return $this->lat;
		}

		/**
		 * @return float
		 */
		public function getLng() {
			return $this->lng;
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