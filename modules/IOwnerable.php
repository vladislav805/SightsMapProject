<?php

	interface IOwnerable extends IItem {

		/**
		 * Returns user ID of owner
		 * @return int
		 */
		function getOwnerId();

	};