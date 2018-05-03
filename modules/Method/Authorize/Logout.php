<?php

	namespace Method\Authorize;

	use Method\APIPrivateMethod;
	use Model\IController;

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
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("DELETE FROM `authorize` WHERE `authKey` = ? LIMIT 1");
			$stmt->execute([$this->authKey]);
			return (boolean) $stmt->rowCount();
		}
	}