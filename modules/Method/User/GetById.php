<?php

	namespace Method\User;

	use Method\APIException;
	use Model\IController;
	use Model\User;
	use tools\DatabaseConnection;

	class GetById extends GetByIds {

		protected $userId;

		public function __construct($request) {
			parent::__construct($request);
			if ($this->userId) {
				$this->userIds = [$this->userId];
			}
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