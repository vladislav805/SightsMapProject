<?php

	namespace Model;

	class ReportReason implements IItem {

		use APIModelGetterFields;

		/** @var int */
		protected $reasonId;

		/** @var string */
		protected $label;

		/**
		 * mark constructor.
		 * @param array $d
		 */
		public function __construct($d) {
			$this->reasonId = (int) $d["reasonId"];
			$this->label = $d["label"];
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->reasonId;
		}

		/**
		 * @return string
		 */
		public function getLabel() {
			return $this->label;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return get_object_vars($this);
		}

	}