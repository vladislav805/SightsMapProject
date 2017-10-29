<?php

	use tools\DatabaseConnection;

	abstract class APIMethod extends Method implements IMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController             $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 */
		abstract function call(\IController $main, DatabaseConnection $db);

	}