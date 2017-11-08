<?php

	namespace Model;

	class Comment implements \IItem, \IDateable {

		const CAN_REMOVE = 1;

		/** @var int */
		private $commentId;

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
			$this->commentId = (int) $p["commentId"];
			$this->userId = (int) $p["userId"];
			$this->date = (int) $p["date"];
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
		 * @return int
		 */
		public function getDate() {
			return $this->date;
		}

		/**
		 * Specify data which should be serialized to JSON
		 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
		 * @return mixed data which can be serialized by <b>json_encode</b>,
		 * which is a value of any type other than a resource.
		 * @since 5.4.0
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"commentId" => $this->commentId,
				"userId" => $this->userId,
				"date" => $this->date,
				"text" => $this->text,
				"canRemove" => (boolean) ($this->extra & self::CAN_REMOVE)
			];
		}

		/**
		 * @param int $currentUserId
		 * @return $this
		 */
		public function setCurrentUser($currentUserId) {
			$this->extra |= $currentUserId === $this->userId ? self::CAN_REMOVE : 0;
			return $this;
		}
	}