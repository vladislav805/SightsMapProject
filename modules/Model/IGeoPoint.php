<?php

	namespace Model;

	interface IGeoPoint extends IItem {

		/**
		 * Returns latitude
		 * @return double
		 */
		public function getLat();

		/**
		 * Returns longitude
		 * @return double
		 */
		public function getLng();

	};