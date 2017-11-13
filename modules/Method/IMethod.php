<?php

	namespace Method;

	use Model\IController;
	use tools\DatabaseConnection;

	interface IMethod {

		/**
		 * Realization of some action
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 */
		public function resolve(IController $main, DatabaseConnection $db);

	};