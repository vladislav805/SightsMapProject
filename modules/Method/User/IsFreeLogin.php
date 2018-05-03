<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	class IsFreeLogin extends APIPublicMethod {

		/** @var string */
		protected $login;

		public function __construct($request) {
			parent::__construct($request);
		}

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