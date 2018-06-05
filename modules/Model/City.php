<?

	namespace Model;

	class City implements IItem {

		/** @var int */
		private $cityId;

		/** @var string */
		private $title;


		public function __construct($d) {
			$this->cityId = (int) $d["cityId"];
			$this->title = $d["title"];
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
		public function getTitle() {
			return $this->title;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"cityId" => $this->cityId,
				"title" => $this->title
			];
		}
	}