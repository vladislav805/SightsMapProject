<?

	namespace Model;

	class Point extends GeoPoint implements \IGeoPoint, \IOwnerable, \IDateable {

		const CAN_MODIFY = 1;
		const IS_VISITED = 4;

		/** @var int */
		private $ownerId;

		/** @var int */
		private $pointId;

		/** @var int[] */
		private $markIds = [];

		/** @var int */
		private $dateCreated;

		/** @var int */
		private $dateUpdated;

		/** @var string */
		private $title;

		/** @var string */
		private $description;

		/** @var boolean */
		private $isVerified;

		/** @var int */
		private $extra;

		/** @var int */
		private $visitState = 0;

		/**
		 * Placemark constructor.
		 * @param array $p
		 */
		public function __construct($p) {
			parent::__construct((double) $p["lat"], (double) $p["lng"]);

			$this->pointId = (int) $p["pointId"];
			$this->ownerId = (int) $p["ownerId"];

			$this->dateCreated = (int) $p["dateCreated"];
			$this->dateUpdated = (int) $p["dateUpdated"];

			$this->title = $p["title"];
			$this->description = $p["description"];

			$this->isVerified = (boolean) $p["isVerified"];
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->pointId;
		}

		/**
		 * @return int
		 */
		public function getOwnerId() {
			return $this->ownerId;
		}

		/**
		 * @return int
		 */
		public function getDate() {
			return $this->dateCreated;
		}

		/**
		 * @param User $user
		 * @return $this
		 */
		public function setAccessByCurrentUser($user) {
			$isHost = $user->getId() == $this->getOwnerId();
			$this->extra |= $isHost ? self::CAN_MODIFY : 0;
			return $this;
		}

		/**
		 * @param int[] $markIds
		 * @return $this
		 */
		public function setMarks($markIds) {
			$this->markIds = $markIds;
			return $this;
		}

		/**
		 * @param boolean $state
		 * @return $this
		 */
		public function setVisitState($state) {
			$this->visitState = $state;
			return $this;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"ownerId" => $this->ownerId,
				"pointId" => $this->pointId,
				"markIds" => $this->markIds,
				"lat" => $this->lat,
				"lng" => $this->lng,
				"dateCreated" => $this->dateCreated,
				"dateUpdated" => $this->dateUpdated,
				"title" => (string) $this->title,
				"description" => $this->description,
				"isVerified" => $this->isVerified,
				"visitState" => $this->visitState,
				"canModify" => (boolean) ($this->extra & self::CAN_MODIFY)
			];
		}

	}