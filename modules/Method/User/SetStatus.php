<?php

	namespace Method\User;

	use Method\APIPrivateMethod;
	use Model\IController;
	use tools\DatabaseConnection;

	/**
	 * Изменение "статуса" онлайна пользователя
	 * @package Method\User
	 */
	class SetStatus extends APIPrivateMethod {

		/** @var  int */
		protected $status;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$stmt = $main->makeRequest("UPDATE `user` SET `lastSeen` = ? WHERE `userId` = ?");

			$stmt->execute([$this->status ? time() : 0, $main->getSession()->getUserId()]);

			return (boolean) $stmt->rowCount();
		}
	}