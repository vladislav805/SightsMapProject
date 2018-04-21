<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class IsFreeLogin extends APIPublicMethod {

		/** @var string */
		protected $login;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `user` WHERE `login` = ?");
			$stmt->execute([$this->login]);
			return !$stmt->fetch(PDO::FETCH_ASSOC)["count"];
		}
	}