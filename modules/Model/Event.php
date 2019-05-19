<?php

	namespace Model;


	class Event implements IItem, IDateable {

		use APIModelGetterFields;

		const EVENT_SIGHT_VERIFIED = 1;
		const EVENT_SIGHT_REMOVED = 2;
//		const EVENT_PHOTO_SUGGESTED = 3;
		const EVENT_SIGHT_COMMENT_ADD = 8;
		const EVENT_SIGHT_ARCHIVED = 12;
		const EVENT_SIGHT_RATING_UP = 14;
		const EVENT_SIGHT_RATING_DOWN = 15;

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

		/** @var string */
		protected $extraText = null;

		public function __construct($d) {
			$this->eventId = (int) $d["eventId"];
			$this->date = (int) $d["date"];
			$this->type = (int) $d["type"];
			$this->ownerUserId = (int) $d["ownerUserId"];
			$this->actionUserId = (int) $d["actionUserId"];
			$this->subjectId = (int) $d["subjectId"];
			$this->isNew = (boolean) $d["isNew"];
			isset($d["extraText"]) && ($this->extraText = $d["extraText"]);
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
		 * @return boolean
		 */
		public function isNew() {
			return $this->isNew;
		}

		/**
		 * @return string
		 */
		public function getExtraText() {
			return $this->extraText;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$res = [
				"eventId" => $this->eventId,
				"date" => $this->date,
				"type" => $this->type,
				"ownerUserId" => $this->ownerUserId,
				"actionUserId" => $this->actionUserId,
				"subjectId" => $this->subjectId,
				"isNew" => $this->isNew
			];

			if ($this->extraText != null) {
				$res += ["extraText" => $this->extraText];
			}

			return $res;
		}
	}