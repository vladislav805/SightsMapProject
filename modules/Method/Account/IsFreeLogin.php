<?php

	namespace Method\Account;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	class IsFreeLogin extends APIPublicMethod {

		/** @var string */
		protected $login;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `user` WHERE `login` = ?");
			$stmt->execute([$this->login]);
			return !$stmt->fetch(PDO::FETCH_ASSOC)["count"];
		}
	}