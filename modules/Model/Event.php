<?php

	namespace Model;


	class Event implements IItem, IDateable {

		use APIModelGetterFields;

		const EVENT_POINT_VERIFIED = 1;
//		const EVENT_PHOTO_SUGGESTED = 3;
		const EVENT_POINT_COMMENT_ADD = 8;
		const EVENT_POINT_ARCHIVED = 12;
		const EVENT_POINT_RATING_UP = 14;
		const EVENT_POINT_RATING_DOWN = 15;

		/** @var int */
		protected $eventId;

		/** @var int */
		protected $type;

		/** @var int */
		protected $date;

		/** @var int */
		protected $ownerUserId;

		/** @var int */
		protected $actionUserId;

		/** @var int */
		protected $subjectId;

		/** @var boolean */
		protected $isNew;

		public function __construct($d) {
			$this->eventId = (int) $d["eventId"];
			$this->date = (int) $d["date"];
			$this->type = (int) $d["type"];
			$this->ownerUserId = (int) $d["ownerUserId"];
			$this->actionUserId = (int) $d["actionUserId"];
			$this->subjectId = (int) $d["subjectId"];
			$this->isNew = (boolean) $d["isNew"];
		}

		/**
		 * Returns date in unixtime format, which object was created
		 * @return int
		 */
		public function getDate() {
			return $this->date;
		}

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId() {
			return $this->eventId;
		}

		/**
		 * Returns ID of event type
		 * @return int
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * Returns owner of ths event
		 * @return int
		 */
		public function getOwnerUserId() {
			return $this->ownerUserId;
		}

		/**
		 * Returns another user, which initiated this event
		 * @return int
		 */
		public function getActionUserId() {
			return $this->actionUserId;
		}

		/**
		 * Returns ID of object, which this event was occurred
		 * @return int
		 */
		public function getSubjectId() {
			return $this->subjectId;
		}

		/**
		 * Specify data which should be serialized to JSON
		 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
		 * @return mixed data which can be serialized by <b>json_encode</b>,
		 * which is a value of any type other than a resource.
		 * @since 5.4.0
		 */
		public function jsonSerialize() {
			return [
				"eventId" => $this->eventId,
				"date" => $this->date,
				"type" => $this->type,
				"ownerUserId" => $this->ownerUserId,
				"actionUserId" => $this->actionUserId,
				"subjectId" => $this->subjectId,
				"isNew" => $this->isNew
			];
		}
	}