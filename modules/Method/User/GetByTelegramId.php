<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\User;
	use PDO;

	/**
	 * @package Method\User
	 */
	class GetByTelegramId extends APIPublicMethod {

		/** @var int */
		protected $telegramId;

		/**
		 * @param IController $main
		 * @return User
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `user` JOIN `city` ON `user`.`cityId` = `city`.`cityId` WHERE `user`.`telegramId` = :tid");

			$stmt->execute([
				":tid" => $this->telegramId
			]);

			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			return $user ? new User($user) : null;
		}
	}