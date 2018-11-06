<?php

	namespace Model;

	interface IDateable {

		/**
		 * Returns date in unixtime format, which object was created
		 * @return int
		 */
		public function getDate();

	}