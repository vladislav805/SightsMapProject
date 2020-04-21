<?php

	namespace Model;

	class Comment implements IItem, IDateable {

		use APIModelGetterFields;

		const CAN_REMOVE = 1;

		/** @var int */
		private $commentId;

		/** @var int */
		private $sightId;

		/** @var int */
		private $userId;

		/** @var int */
		private $date;

		/** @var string */
		private $text;

		/** @var int */
		private $extra = 0;

		/**
		 * Comment constructor.
		 * @param $p
		 */
		public function __construct($p) {
			isset($p["commentId"]) && ($this->commentId = (int) $p["commentId"]);
			isset($p["sightId"]) && ($this->sightId = (int) $p["sightId"]);
			$this->userId = (int) $p["userId"];
			isset($p["date"]) && ($this->date = (int) $p["date"]);
			$this->text = $p["text"];
		}

		/**
		 * @return int
		 */
		public function getUserId() {
			return $this->userId;
		}

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId() {
			return $this->commentId;
		}

		/**
		 * @param int $commentId
		 * @return Comment
		 */
		public function setId(int $commentId) {
			$this->commentId = $commentId;
			return $this;
		}

		/**
		 * @return int
		 */
		public function getSightId() {
			return $this->sightId;
		}

		/**
		 * @return int
		 */
		public function getDate() {
			return $this->date;
		}

		/**
		 * @return string
		 */
		public function getText() {
			return $this->text;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"commentId" => $this->commentId,
				"userId" => $this->userId,
				"date" => $this->date,
				"text" => $this->text,
				"canModify" => (boolean) ($this->extra & self::CAN_REMOVE)
			];
		}

		/**
		 * @param boolean $state
		 * @return $this
		 */
		public function setCanEdit($state) {
			$this->extra = $state ? self::CAN_REMOVE : 0;
			return $this;
		}
	}