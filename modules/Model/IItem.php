<?php

	namespace Model;

	interface IItem extends \JsonSerializable {

		/**
		 * Returns ID of object
		 * @return int
		 */
		public function getId();

	};