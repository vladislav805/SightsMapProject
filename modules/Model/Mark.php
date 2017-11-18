<?php

	namespace Model;

	class Mark implements IItem {

		/** @var int */
		protected $markId;

		/** @var string */
		protected $title;

		/** @var int */
		protected $color;

		/**
		 * mark constructor.
		 * @param array $d
		 */
		public function __construct($d) {
			$this->markId = (int) $d["markId"];
			$this->title = $d["title"];
			$this->color = (int) $d["color"];
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->markId;
		}

		/**
		 * @return string
		 */
		public function getTitle() {
			return $this->title;
		}

		/**
		 * @return int
		 */
		public function getColor() {
			return $this->color;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"markId" => $this->markId,
				"title" => $this->title,
				"color" => $this->color
			];
		}

	}