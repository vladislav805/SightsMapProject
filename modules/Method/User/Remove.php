<?php

	namespace Method\User;

	use IController;
	use APIException;
	use APIPrivateMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $userId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			//$sql = sprintf("DELETE FROM `user` WHERE `userId` = '%d'", $this->userId);

			return false; //$db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}