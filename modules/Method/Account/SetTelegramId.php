<?php

	namespace Method\Account;

	use Method\APIPublicMethod;
	use Model\IController;

	/**
	 * @package Method\User
	 */
	class SetTelegramId extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/** @var int */
		protected $telegramId;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("UPDATE `user` SET `user`.`telegramId` = :tid WHERE `user`.`userId` = :uid");

			$stmt->execute([
				":uid" => $this->userId,
				":tid" => $this->telegramId
			]);

			return (boolean) $stmt->rowCount();
		}
	}