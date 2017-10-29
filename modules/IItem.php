<?php

	interface IItem extends \JsonSerializable {

		/**
		 * Returns ID of object
		 * @return int
		 */
		function getId();

	};