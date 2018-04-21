<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class IsFreeEmail extends APIPublicMethod {

		/** @var string */
		protected $email;

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
			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `user` WHERE `email` = ?");
			$stmt->execute([$this->email]);
			return !$stmt->fetch(PDO::FETCH_ASSOC)["count"];
		}
	}