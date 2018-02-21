<?

	namespace tools;

	use InvalidArgumentException;

	class ExifGPSPoint {

		/** @var array */
		private $exif;
		
		/** @var double */
		private $latitude;
		
		/** @var double */
		private $longitude;

		public function __construct($exif) {
			$this->exif = $exif;
			if (!$this->exif) {
				throw new InvalidArgumentException("Argument is null");
			}

			$this->read();
		}

		/**
		 * Reading exif marks
		 */
		private function read() {
			if ($this->hasGPSMark()) {
				$this->latitude = $this->toFloatCoordinate($this->exif["GPSLatitude"], $this->exif["GPSLatitudeRef"]);
				$this->longitude = $this->toFloatCoordinate($this->exif["GPSLongitude"], $this->exif["GPSLongitudeRef"]);
			}
		}

		/**
		 * Has photo latitude/longitude exif-marks?
		 * @return boolean
		 */
		public function hasGPSMark() {
			return isset($this->exif["GPSLatitudeRef"]) && isset($this->exif["GPSLatitude"]) && isset($this->exif["GPSLongitudeRef"]) && isset($this->exif["GPSLongitude"]);
		}

		/**
		 * Convert deg+min+sec string to number
		 * @param string $value
		 * @param string $ref
		 * @return float
		 */
		private function toFloatCoordinate($value, $ref) {
			list($deg, $min, $sec) = $value;
			$d = $this->simplify($deg) + (($this->simplify($min) / 60) + ($this->simplify($sec) / 3600));
			return ($ref === "S" || $ref === "W") ? -$d : $d;
		}

		/**
		 * Simplify number from expression in string
		 * @param string|float $value
		 * @return double
		 */
		private function simplify($value) {
			if (strpos($value, "/") !== false) {
				list($base, $divider) = explode("/", $value, 2);
				if ($divider == 0) {
					return 0;
				}
				$value = $base / $divider;
			}

			return (double) $value;
		}

		/**
		 * @return float
		 */
		public function getLatitude() {
			return $this->latitude;
		}

		/**
		 * @return float
		 */
		public function getLongitude() {
			return $this->longitude;
		}

	}