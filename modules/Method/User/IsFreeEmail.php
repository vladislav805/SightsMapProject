<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	class IsFreeEmail extends APIPublicMethod {

		/** @var string */
		protected $email;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `user` WHERE `email` = ?");
			$stmt->execute([$this->email]);
			return !$stmt->fetch(PDO::FETCH_ASSOC)["count"];
		}
	}