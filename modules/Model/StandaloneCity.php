<?

	namespace Model;

	class StandaloneCity extends City implements IGeoPoint {

		/** @var int|null */
		protected $count = null;

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

		/** @var int */
		protected $radius;

		/** @var string */
		protected $description;

		public function __construct($d) {
			parent::__construct($d);

			$this->lat = (double) $d["lat"];
			$this->lng = (double) $d["lng"];

			isset($d["count"]) && ($this->count = (int) $d["count"]);
			isset($d["radius"]) && ($this->radius = (int) $d["radius"]);
			isset($d["description"]) && ($this->description = $d["description"]);
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

		public function jsonSerialize() {
			$res = parent::jsonSerialize();

			$res["lat"] = $this->lat;
			$res["lng"] = $this->lng;

			if ($this->count !== null) {
				$res["count"] = $this->count;
			}

			if ($this->radius) {
				$res["radius"] = $this->radius;
			}

			return $res;
		}

	}