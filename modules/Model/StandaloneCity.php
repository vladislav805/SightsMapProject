<?

	namespace Model;

	class StandaloneCity extends City implements IGeoPoint {

		/** @var int|null */
		protected $count = null;

		public function __construct($d) {
			parent::__construct($d);

			$this->lat = (double) $d["lat"];
			$this->lng = (double) $d["lng"];

			isset($d["count"]) && ($this->count = (int) $d["count"]);
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