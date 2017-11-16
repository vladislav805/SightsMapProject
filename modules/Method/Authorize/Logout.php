<?php

	namespace Method\Authorize;

	use Method\APIPrivateMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Logout extends APIPrivateMethod {

		/** @var string */
		protected $authKey;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return bool
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("DELETE FROM `authorize` WHERE `authKey` = '%s' LIMIT 1", $this->authKey);

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}