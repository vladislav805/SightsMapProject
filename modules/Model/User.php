<?

	namespace Model;

	class User implements IItem {

		/** @var int */
		private $userId;

		/** @var string */
		private $login;

		/** @var string */
		private $email;

		/** @var string */
		private $firstName;

		/** @var string */
		private $lastName;

		/** @var int */
		private $sex;

		/** @var int */
		private $lastSeen;

		/** @var boolean */
		private $isOnline;

		/** @var Photo */
		private $photo;

		/**
		 * User constructor.
		 * @param array $u
		 */
		public function __construct($u) {
			$this->userId = (int) $u["userId"];
			$this->login = $u["login"];
			$this->email = $u["email"];
			$this->firstName = $u["firstName"];
			$this->lastName = $u["lastName"];
			$this->sex = (int) $u["sex"];
			$this->lastSeen = (int) $u["lastSeen"];
			$this->isOnline = (boolean) ($u["lastSeen"] > time() - 300);
			$this->photo = new Photo($u);
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->userId;
		}

		/**
		 * @return Photo
		 */
		public function getPhoto() {
			return $this->photo;
		}

		/**
		 * @return string
		 */
		public function getFirstName() {
			return $this->firstName;
		}

		/**
		 * @return string
		 */
		public function getLastName() {
			return $this->lastName;
		}

		/**
		 * @return int
		 */
		public function getLastSeen() {
			return $this->lastSeen;
		}

		/**
		 * @return string
		 */
		public function getLogin() {
			return $this->login;
		}

		/**
		 * @return string
		 */
		public function getEmail() {
			return $this->email;
		}

		/**
		 * @return int
		 */
		public function getSex() {
			return $this->sex;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"userId" => $this->userId,
				"login" => $this->login,
				"firstName" => $this->firstName,
				"lastName" => $this->lastName,
				"sex" => $this->sex,
				"lastSeen" => $this->lastSeen,
				"isOnline" => $this->isOnline,
				"photo" => $this->photo
			];
		}

	}