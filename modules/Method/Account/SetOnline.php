<?php

	namespace Method\Account;

	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Изменение "статуса" онлайна пользователя
	 * @package Method\User
	 */
	class SetOnline extends APIPrivateMethod {

		/** @var  int */
		protected $status;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("UPDATE `user` SET `lastSeen` = ? WHERE `userId` = ?");

			$stmt->execute([$this->status ? time() : 0, $main->getSession()->getUserId()]);

			return (boolean) $stmt->rowCount();
		}
	}