<?php

	use tools\DatabaseConnection;

	abstract class APIPublicMethod extends APIMethod {

		/**
		 * APIPublicMethod constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 */
		public function call(\IController $main, DatabaseConnection $db) {
			return $this->resolve($main, $db);
		}

	}