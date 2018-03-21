<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
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
			$sql = sprintf("SELECT COUNT(*) FROM `user` WHERE `email` = '%s'", $this->email);

			return !$db->query($sql, DatabaseResultType::COUNT);
		}
	}