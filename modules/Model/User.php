<?

	namespace Model;

	class User implements \IItem {

		/** @var int */
		private $userId;

		/** @var string */
		private $login;

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