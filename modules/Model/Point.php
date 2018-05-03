<?

	namespace Model;

	class Point extends GeoPoint implements IGeoPoint, IOwnerable, IDateable {

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

		/** @var float */
		private $rating = 0;

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

			isset($p["rating"]) && ($this->rating = (float) $p["rating"]);
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
		 * @return int
		 */
		public function getDateUpdated() {
			return $this->dateUpdated;
		}

		/**
		 * @return string
		 */
		public function getTitle() {
			return $this->title;
		}

		/**
		 * @return string
		 */
		public function getDescription() {
			return $this->description;
		}

		/**
		 * @return int[]
		 */
		public function getMarkIds() {
			return $this->markIds;
		}

		/**
		 * @param User $user
		 * @deprecated
		 * @return $this
		 */
		public function setAccessByCurrentUser($user) {
			$isHost = $user->getId() == $this->getOwnerId() || $user->getId() < ADMIN_ID_LIMIT;
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
		 * @param int $state
		 * @return $this
		 */
		public function setVisitState($state) {
			$this->visitState = $state;
			return $this;
		}

		/**
		 * @return int
		 */
		public function getVisitState() {
			return $this->visitState;
		}

		/**
		 * @return float
		 */
		public function getRating() {
			return $this->rating;
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
				"rating" => $this->rating,
				"canModify" => (boolean) ($this->extra & self::CAN_MODIFY)
			];
		}

	}