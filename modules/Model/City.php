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

		/** @var int */
		protected $radius;

		/** @var string */
		protected $description;


		public function __construct($d) {
			isset($d["cityId"]) && ($this->cityId = (int) $d["cityId"]);
			$this->name = $d["name"];
			isset($d["parentId"]) && ($this->parentId = (int) $d["parentId"]);
			isset($d["lat"]) && ($this->lat = (double) $d["lat"]);
			isset($d["lng"]) && ($this->lng = (double) $d["lng"]);
			isset($d["radius"]) && ($this->radius = (int) $d["radius"]);
			isset($d["description"]) && ($this->description = $d["description"]);
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
		 * @return int
		 */
		public function getRadius() {
			return $this->radius;
		}

		/**
		 * @return string
		 */
		public function getDescription() {
			return $this->description;
		}

		/**
		 * @param string $name
		 * @return City
		 */
		public function setName($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param int|null $parentId
		 * @return City
		 */
		public function setParentId($parentId) {
			$this->parentId = $parentId;
			return $this;
		}

		/**
		 * @param float $lat
		 * @return City
		 */
		public function setLat($lat) {
			$this->lat = $lat;
			return $this;
		}

		/**
		 * @param float $lng
		 * @return City
		 */
		public function setLng($lng) {
			$this->lng = $lng;
			return $this;
		}

		/**
		 * @param int $radius
		 * @return City
		 */
		public function setRadius($radius) {
			$this->radius = $radius;
			return $this;
		}

		/**
		 * @param string $description
		 * @return City
		 */
		public function setDescription($description) {
			$this->description = $description;
			return $this;
		}



		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"cityId" => $this->cityId,
				"name" => $this->name,
				"radius" => $this->radius,
				"parentId" => $this->parentId > 0 ? $this->parentId : null
			];
		}
	}