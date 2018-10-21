<?

	namespace Model;

	use JsonSerializable;

	class PrevailColor implements JsonSerializable {

		/** @var int */
		private $color;

		/** @var int */
		private $value;

		/**
		 * PrevailColor constructor.
		 * @param int $color
		 * @param int $val
		 */
		public function __construct($color, $val) {
			$this->color = $color;
			$this->value = $val;
		}

		/**
		 * @param string $str
		 * @return PrevailColor
		 */
		public static function parse($str) {
			list($hex, $val) = explode(",", $str);
			return new PrevailColor(hexdec($hex), (int) $val);
		}

		/**
		 * @return string
		 */
		public function serialize() {
			return sprintf("%06x,%d", $this->color, $this->value);
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$res = get_object_vars($this);
			$res["hex"] = sprintf("%06x", $this->color);
			return $res;
		}
	}