<?

	namespace Model;

	class AuthKey implements IItem, IDateable {

		use APIModelGetterFields;

		/** @var int */
		protected $authId;

		/** @var string */
		protected $authKey;

		/** @var int */
		protected $userId;

		/** @var int */
		protected $date;

		/**
		 * AuthKey constructor.
		 * @param array $d
		 */
		public function __construct($d) {
			$this->authId = (int) $d["authId"];
			$this->authKey = $d["authKey"];
			$this->userId = (int) $d["userId"];
			$this->date = (int) $d["date"];
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->authId;
		}

		/**
		 * @return int
		 */
		public function getUserId() {
			return $this->userId;
		}

		/**
		 * @return string
		 */
		public function getAuthKey() {
			return $this->authKey;
		}

		/**
		 * @return int
		 */
		public function getDate() {
			return $this->date;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"authId" => $this->authId,
				"authKey" => $this->authKey,
				"userId" => $this->userId,
				"date" => $this->date
			];
		}

	}