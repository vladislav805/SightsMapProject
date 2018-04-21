<?php

	namespace Method\User;

	use Method\APIPrivateMethod;
	use Model\IController;
	use tools\DatabaseConnection;

	/**
	 * Удаление пользователя. Убрано намеренно.
	 * @package Method\User
	 */
	class Remove extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			return false;
		}
	}