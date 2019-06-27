<?php

	namespace Model;

	class Collection implements IItem, IDateable {

		const TYPE_PUBLIC = "PUBLIC";
		const TYPE_PRIVATE = "PRIVATE";

		/** @var int */
		private $collectionId;

		/** @var int */
		private $ownerId;

		/** @var int */
		private $title;

		/** @var int */
		private $description;

		/** @var int */
		private $dateCreated;

		/** @var int */
		private $dateUpdated;

		/** @var string */
		private $type;

		/** @var int */
		private $cityId;

		/** @var User|null */
		private $author;

		/** @var City|null */
		private $city;

		/** @var int */
		private $__extra = 0;

		const CAN_MODIFY = 1;

		/**
		 * Comment constructor.
		 * @param $p
		 */
		public function __construct($p) {
			isset($p["collectionId"]) && ($this->collectionId = (int) $p["collectionId"]);
			$this->ownerId = (int) $p["ownerId"];
			$this->title = $p["title"];
			$this->description = $p["description"];
			$this->dateCreated = (int) $p["dateCreated"];
			isset($p["dateUpdated"]) && ($this->dateUpdated = (int) $p["dateUpdated"]);
			$this->type = $p["type"];

			if (((int) $p["cityId"]) > 0) {
				$this->city = new City(get_object_of_prefix($p, "city"));
			}
		}

		/**
		 * @return int
		 */
		public function getCollectionId() {
			return $this->collectionId;
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
		public function getTitle() {
			return $this->title;
		}

		/**
		 * @return int
		 */
		public function getDescription() {
			return $this->description;
		}

		/**
		 * @return int
		 */
		public function getDateCreated() {
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
		public function getType() {
			return $this->type;
		}

		/**
		 * @return int
		 */
		public function getCityId() {
			return $this->cityId;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"collectionId" => $this->collectionId,
				"ownerId" => $this->ownerId,
				"title" => $this->title,
				"description" => $this->description,
				"type" => $this->type,
				"dateCreated" => $this->dateCreated,
				"dateUpdated" => $this->dateUpdated,
				"city" => $this->city,
				"author" => $this->author,
				"canModify" => (boolean) ($this->__extra & self::CAN_MODIFY)
			];
		}

		/**
		 * Returns date in unixtime format, which object was created
		 * @return int
		 */
		public function getDate() {
			return $this->getDateCreated();
		}

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId() {
			return $this->getCollectionId();
		}

		/**
		 * @param int $title
		 * @return Collection
		 */
		public function setTitle(int $title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @param int $description
		 * @return Collection
		 */
		public function setDescription(int $description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param string $type
		 * @return Collection
		 */
		public function setType(string $type) {
			$this->type = $type;
			return $this;
		}

		/**
		 * @param int $cityId
		 * @return Collection
		 */
		public function setCityId(int $cityId) {
			$this->cityId = $cityId;
			return $this;
		}


	}