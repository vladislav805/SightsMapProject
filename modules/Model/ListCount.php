<?php

	namespace Model;

	use JsonSerializable;

	class ListCount implements JsonSerializable {

		/** @var int */
		private $count;

		/** @var mixed */
		private $items;

		/** @var array */
		private $custom = [];

		/**
		 * ListCount constructor.
		 * @param int $count
		 * @param mixed $items
		 */
		public function __construct($count = 0, $items = []) {
			$this->count = $count;
			$this->items = $items;
		}

		/**
		 * @param string $key
		 * @param mixed $data
		 * @return $this
		 */
		public function putCustomData($key, $data) {
			$this->custom[$key] = $data;
			return $this;
		}

		/**
		 * @param string $key
		 * @return mixed|null
		 */
		public function getCustomData($key) {
			return isset($this->custom[$key]) ? $this->custom[$key] : null;
		}

		/**
		 * @return int
		 */
		public function getCount() {
			return $this->count;
		}

		/**
		 * @return mixed
		 */
		public function getItems() {
			return $this->items;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$data = [
				"count" => $this->count,
				"items" => $this->items
			];

			foreach ($this->custom as $key => $item) {
				$data[$key] = $item;
			}

			return $data;
		}

	}