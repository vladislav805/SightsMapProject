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
		protected $access;

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
			$this->access = (int) ($d["access"] ?? 0);
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
		 * @return int
		 */
		public function getAccess() {
			return $this->access;
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
				"access" => $this->access,
				"date" => $this->date
			];
		}

	}