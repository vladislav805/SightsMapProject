<?php

	interface IMethod {

		/**
		 * Realization of some action
		 * @param IController		$main
		 * @param \tools\DatabaseConnection $db
		 * @return mixed
		 */
		public function resolve(\IController $main, \tools\DatabaseConnection $db);

	};