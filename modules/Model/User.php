<?

	namespace Model;

	class User implements IItem {

		use APIModelGetterFields;

		const STATE_INACTIVE = "INACTIVE";
		const STATE_USER = "USER";
		const STATE_MODERATOR = "MODERATOR";
		const STATE_ADMIN = "ADMIN";
		const STATE_BANNED = "BANNED";

		const GENDER_NOT_SET = "NOT_SET";
		const GENDER_FEMALE = "FEMALE";
		const GENDER_MALE = "MALE";

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

		/** @var string */
		private $sex;

		/** @var string */
		private $bio = false;

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

		/** @var int|boolean */
		private $rating = false;

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
			$this->sex = $u["sex"];

			if (isset($u["bio"])) {
				$this->bio = $u["bio"];
			}
			
			$this->lastSeen = (int) $u["lastSeen"];
			$this->online = (boolean) ($u["lastSeen"] > time() - 300);

			if (array_key_exists("photo200", $u)) {
				$this->photo = new Photo($u);
			}

			if (isset($u["cityId"]) && $u["cityId"] && isset($u["name"])) {
				$this->city = new City($u);
			}

			isset($u["status"]) && ($this->status = $u["status"]);
			array_key_exists("rating", $u) && ($this->rating = (int) $u["rating"]);
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
		 * @return string
		 */
		public function getSex() {
			return $this->sex;
		}

		/**
		 * @return string
		 */
		public function getBio() {
			return $this->bio;
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
		 * @return int
		 */
		public function getRating() {
			return $this->rating;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$u = [
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

			if ($this->bio !== false) {
				$u["bio"] = $this->bio;
			}

			if ($this->rating !== false) {
				$u["rating"] = $this->rating;
			}

			return $u;
		}

	}