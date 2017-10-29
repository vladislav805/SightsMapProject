<?php

	interface IGeoPoint extends IItem {

		/**
		 * Returns latitude
		 * @return double
		 */
		function getLat();

		/**
		 * Returns longitude
		 * @return double
		 */
		function getLng();

	};