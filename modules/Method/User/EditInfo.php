<?php

	namespace Method\User;

	use IController;
	use APIException;
	use APIPrivateMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class EditInfo extends APIPrivateMethod {

		/** @var string */
		protected $firstName;

		/** @var  string */
		protected $lastName;

		/** @var int */
		protected $sex;

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
			$sql = sprintf("UPDATE `user` SET `firstName` = '%s', `lastName` = '%s', `sex` = '%d' WHERE `userId` = '%d'", $this->firstName, $this->lastName, $this->sex, $main->getSession()->getUserId());

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}