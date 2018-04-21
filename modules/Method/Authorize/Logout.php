<?php

	namespace Method\Authorize;

	use Method\APIPrivateMethod;
	use Model\IController;
	use tools\DatabaseConnection;

	/**
	 * Завершение сессии и удаление токена
	 * @package Method\Authorize
	 */
	class Logout extends APIPrivateMethod {

		/** @var string */
		protected $authKey;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$stmt = $main->makeRequest("DELETE FROM `authorize` WHERE `authKey` = ? LIMIT 1");
			$stmt->execute([$this->authKey]);
			return (boolean) $stmt->rowCount();
		}
	}