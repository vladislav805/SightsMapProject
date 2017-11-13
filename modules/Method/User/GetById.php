<?php

	namespace Method\User;

	use Method\APIException;
	use Model\IController;
	use Model\User;
	use tools\DatabaseConnection;

	class GetById extends GetByIds {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return User
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$result = parent::resolve($main, $db);
			return $result[0];
		}
	}