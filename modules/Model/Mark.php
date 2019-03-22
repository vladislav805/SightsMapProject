<?php

	namespace Model;

	class Mark implements IItem {

		use APIModelGetterFields;

		/** @var int */
		protected $markId;

		/** @var string */
		protected $title;

		/** @var int */
		protected $color;

		/** @var int|null */
		protected $count = null;

		/**
		 * mark constructor.
		 * @param array $d
		 */
		public function __construct($d) {
			isset($d["markId"]) && ($this->markId = (int) $d["markId"]);
			$this->title = $d["title"];
			$this->color = (int) $d["color"];
			isset($d["count"]) && ($this->count = (int) $d["count"]);
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
		 * @return int|null
		 */
		public function getCount() {
			return $this->count;
		}

		/**
		 * @param string $title
		 * @return Mark
		 */
		public function setTitle($title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @param int $color
		 * @return Mark
		 */
		public function setColor($color) {
			$this->color = $color;
			return $this;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$res = [
				"markId" => $this->markId,
				"title" => $this->title,
				"color" => $this->color
			];

			if ($this->count !== null) {
				$res["count"] = $this->count;
			}

			return $res;
		}

	}