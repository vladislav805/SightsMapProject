<?php

	namespace Method\User;

	use APIPublicMethod;
	use IController;
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
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT COUNT(*) FROM `user` WHERE `login` = '%s'", $this->login);

			return !$db->query($sql, DatabaseResultType::COUNT);
		}
	}