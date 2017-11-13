<?php

	namespace Method\User;

	use APIPrivateMethod;
	use Method\APIException;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class SetStatus extends APIPrivateMethod {

		/** @var  int */
		protected $status;

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
			$sql = sprintf("UPDATE `user` SET `lastSeen` = '%d' WHERE `userId` = '%d'", $this->status ? time() : 0, $main->getSession()->getUserId());

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}