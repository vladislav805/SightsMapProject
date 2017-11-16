<?php

	namespace Method\User;

	use Method\APIPrivateMethod;
	use Model\IController;
	use Method\APIException;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("DELETE FROM `user` WHERE `userId` = '%d'", $main->getSession()->getUserId());

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}