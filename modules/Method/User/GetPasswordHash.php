<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use tools\DatabaseConnection;

	/**
	 * Высчитывание хэш-суммы от пароля
	 * @package Method\User
	 */
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