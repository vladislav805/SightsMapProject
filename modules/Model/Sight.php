<?

	namespace Model;

	class Sight extends GeoPoint implements IGeoPoint, IOwnerable, IDateable {

		use APIModelGetterFields;

		const CAN_MODIFY = 1;
		const IS_VISITED = 4;

		/** @var int */
		private $ownerId;

		/** @var int */
		private $sightId;

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
		private $isArchived;

		/** @var boolean */
		private $isVerified;

		/** @var float */
		private $rating = 0;

		/** @var int */
		private $rated = 0;

		/** @var int */
		private $extra;

		/** @var int */
		private $visitState = 0;

		/** @var City|null */
		private $city = null;

		/** @var Photo|null */
		private $photo = null;

		/** @var int */
		private $parentId = null;

		/** @var Sight|null */
		private $child = null;

		/** @var Sight|null */
		private $parent = null;

		/** @var double|null */
		private $interest = null;

		/** @var int|null */
		private $comments = null;

		/**
		 * Placemark constructor.
		 * @param array $p
		 */
		public function __construct($p) {
			if (!$p) {
				return;
			}
			parent::__construct((double) $p["lat"], (double) $p["lng"]);

			$this->sightId = (int) $p["sightId"];
			$this->ownerId = (int) $p["ownerId"];

			$this->dateCreated = (int) $p["dateCreated"];
			$this->dateUpdated = (int) $p["dateUpdated"];

			$this->title = $p["title"];
			$this->description = $p["description"];

			$this->isArchived = (boolean) $p["isArchived"];
			$this->isVerified = (boolean) $p["isVerified"];

			isset($p["rating"]) && ($this->rating = (int) $p["rating"]);
			isset($p["rated"]) && ($this->rated = (int) $p["rated"]);

			isset($p["visitState"]) && ($this->visitState = (int) $p["visitState"]);

			if (isset($p["markIds"]) && !is_null($p["markIds"])) {
				$this->markIds = array_map("intval", explode(",", $p["markIds"]));
			}

			if (isset($p["cityId"]) && $p["cityId"] !== null && isset($p["name"])) {
				$this->city = new City($p);
			}

			if (isset($p["photoOwnerId"]) && $p["photoOwnerId"] !== null) {
				$photo = $p;
				$photo["ownerId"] = $photo["photoOwnerId"];
				$photo["date"] = $photo["photoDate"];
				$this->photo = new Photo($photo);
			}

			isset($p["parentId"]) && ($this->parentId = (int) $p["parentId"]);

			isset($p["comments"]) && ($this->comments = (int) $p["comments"]);
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->sightId;
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
		 * @return City|null
		 */
		public function getCity() {
			return $this->city;
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
			$isHost = $user->getId() === $this->getOwnerId() || $user->getStatus() === User::STATE_MODERATOR || $user->getStatus() === User::STATE_ADMIN;
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
		 * @deprecated
		 */
		public function setVisitState($state) {
			$this->visitState = $state;
			return $this;
		}

		/**
		 * @param Sight $sight
		 */
		public function setChild($sight) {
			if (!($sight instanceof Sight)) {
				return;
			}

			$this->child = $sight;
		}

		/**
		 * @param Sight $sight
		 */
		public function setParent($sight) {
			if (!($sight instanceof Sight)) {
				return;
			}

			$this->parent = $sight;
		}

		/**
		 * @param double $interest
		 * @return Sight
		 */
		public function setInterest($interest) {
			$this->interest = $interest;
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
		 * @return int
		 */
		public function getRated() {
			return $this->rated;
		}

		/**
		 * @return Photo|null
		 */
		public function getPhoto() {
			return $this->photo;
		}

		/**
		 * @return boolean
		 */
		public function isVerified() {
			return $this->isVerified;
		}

		/**
		 * @return boolean
		 */
		public function isArchived() {
			return $this->isArchived;
		}

		/**
		 * @return boolean
		 */
		public function canModify() {
			return (boolean) ($this->extra & self::CAN_MODIFY);
		}

		/**
		 * @return int
		 */
		public function getParentId() {
			return $this->parentId;
		}

		/**
		 * @return Sight|null
		 */
		public function getChild() {
			return $this->child;
		}

		/**
		 * @return Sight|null
		 */
		public function getParent() {
			return $this->parent;
		}

		/**
		 * @return double
		 */
		public function getInterest() {
			return $this->interest;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$p = [
				"ownerId" => $this->ownerId,
				"sightId" => $this->sightId,
				"markIds" => $this->markIds,
				"lat" => $this->lat,
				"lng" => $this->lng,
				"dateCreated" => $this->dateCreated,
				"dateUpdated" => $this->dateUpdated,
				"title" => (string) $this->title,
				"description" => $this->description,
				"city" => $this->city,
				"isVerified" => $this->isVerified,
				"isArchived" => $this->isArchived,
				"visitState" => $this->visitState,
				"rating" => [
					"value" => $this->rating,
					"userValue" => $this->rated
				],
				"canModify" => $this->canModify(),
			];

			if ($this->photo) {
				$p["photo"] = $this->photo;
			}

			if ($this->parentId !== null) {
				$p["parentId"] = $this->parentId;
			}

			if ($this->child) {
				$p["child"] = $this->child;
			}

			if ($this->parent) {
				$p["parent"] = $this->parent;
			}

			if ($this->interest !== null) {
				$p["interest"] = [
					"value" => $this->interest,
				];
			}

			if (!is_null($this->comments)) {
				$p["comments"] = [
					"count" => $this->comments,
				];
			}

			return $p;
		}

	}