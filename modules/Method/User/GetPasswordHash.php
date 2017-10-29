<?php

	namespace Method\User;

	use IController;
	use APIPublicMethod;
	use tools\DatabaseConnection;

	class GetPasswordHash extends APIPublicMethod {

		protected $password;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return string
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			return hash("sha512", $this->password . PASSWORD_SALT);
		}
	}