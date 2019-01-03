<?

	namespace Model;

	class User implements IItem {

		use APIModelGetterFields;

		const STATE_INACTIVE = "INACTIVE";
		const STATE_USER = "USER";
		const STATE_MODERATOR = "MODERATOR";
		const STATE_ADMIN = "ADMIN";
		const STATE_BANNED = "BANNED";

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
		private $online;

		/** @var Photo */
		private $photo;

		/** @var City|null */
		private $city;

		/** @var string */
		private $status;

		/**
		 * User constructor.
		 * @param array $u
		 */
		public function __construct($u) {
			$this->userId = (int) $u["userId"];
			$this->login = $u["login"];
			isset($u["email"]) && ($this->email = $u["email"]);
			$this->firstName = $u["firstName"];
			$this->lastName = $u["lastName"];
			$this->sex = (int) $u["sex"];
			$this->lastSeen = (int) $u["lastSeen"];
			$this->online = (boolean) ($u["lastSeen"] > time() - 300);
			$this->photo = new Photo($u);

			if (isset($u["cityId"]) && $u["cityId"] && isset($u["name"])) {
				$this->city = new City($u);
			}

			isset($u["status"]) && ($this->status = $u["status"]);
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
		 * @return City|null
		 */
		public function getCity() {
			return $this->city;
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
		 * @return boolean
		 */
		public function isOnline() {
			return $this->online;
		}

		/**
		 * @return string
		 */
		public function getStatus() {
			return $this->status;
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
				"isOnline" => $this->online,
				"photo" => $this->photo,
				"city" => $this->city
			];
		}

	}