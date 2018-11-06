<?php

	namespace Model;

	interface IOwnerable extends IItem {

		/**
		 * Returns user ID of owner
		 * @return int
		 */
		public function getOwnerId();

	};