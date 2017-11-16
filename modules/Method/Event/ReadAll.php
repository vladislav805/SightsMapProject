<?php

	namespace Method\Event;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class ReadAll extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("UPDATE `event` SET `isNew` = 0 WHERE `ownerUserId` = '%d'", $main->getSession()->getUserId());
			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}