<?php

	namespace Model;

	/**
	 * @deprecated
	 * @package Model
	 */
	final class Params {

		/** @var array */
		private $data;

		/**
		 * Params constructor.
		 * @param array= $p
		 */
		public function __construct($p = []) {
			$this->data = $p;
		}

		/**
		 * @return array
		 */
		public function getAll() {
			return $this->data;
		}

		/**
		 * @param string $key
		 * @param mixed $value
		 * @return $this
		 */
		public function set($key, $value) {
			$this->data[$key] = $value;
			return $this;
		}

		/**
		 * @param string $key
		 * @return mixed
		 */
		public function get($key) {
			return isset($this->data[$key]) ? $this->data[$key] : null;
		}

		/**
		 * @param string $key
		 * @return $this
		 */
		public function remove($key) {
			unset($this->data[$key]);
			return $this;
		}

	}