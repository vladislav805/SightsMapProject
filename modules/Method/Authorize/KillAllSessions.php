<?php

	namespace Method\Authorize;

	use APIPrivateMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class KillAllSessions extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("DELETE FROM `authorize` WHERE `userId` = '%d' LIMIT 1", $main->getSession()->getUserId());

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}