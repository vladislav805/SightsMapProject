<?

	namespace Model;

	class City implements IItem {

		/** @var int */
		private $cityId;

		/** @var string */
		private $name;


		public function __construct($d) {
			$this->cityId = (int) $d["cityId"];
			$this->name = $d["name"];
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
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"cityId" => $this->cityId,
				"name" => $this->name
			];
		}
	}