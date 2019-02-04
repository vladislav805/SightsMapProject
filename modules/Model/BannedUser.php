<?

	namespace Model;

	class BannedUser extends User {

		/** @var int */
		private $banId;

		/** @var int */
		private $reason;

		/** @var string */
		private $comment;

		public function __construct($u) {
			parent::__construct($u);

			$this->banId = (int) $u["banId"];
			$this->reason = (int) $u["reason"];
			$this->comment = $u["comment"];
		}

		/**
		 * @return int
		 */
		public function getBanId() {
			return $this->banId;
		}

		/**
		 * @return int
		 */
		public function getReason() {
			return $this->reason;
		}

		/**
		 * @return string
		 */
		public function getComment() {
			return $this->comment;
		}

		public function jsonSerialize() {
			$res = parent::jsonSerialize() + [
				"banId" => $this->banId,
				"reason" => $this->reason,
				"comment" => $this->comment
			];

			return $res;
		}

	}