<?

	namespace Model;

	class StandaloneCity extends City {

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

		/** @var int|null */
		protected $count = null;

		public function __construct($d) {
			parent::__construct($d);

			$this->lat = (double) $d["lat"];
			$this->lng = (double) $d["lng"];
			isset($d["count"]) && ($this->count = (int) $d["count"]);
		}

		/**
		 * @return float
		 */
		public function getLatitude() {
			return $this->lat;
		}

		/**
		 * @return float
		 */
		public function getLongitude() {
			return $this->lng;
		}

		public function jsonSerialize() {
			$res = parent::jsonSerialize();

			$res["lat"] = $this->lat;
			$res["lng"] = $this->lng;

			if ($this->count !== null) {
				$res["count"] = $this->count;
			}

			return $res;
		}

	}