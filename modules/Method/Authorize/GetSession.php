<?php

	namespace Method\Authorize;

	use APIPublicMethod;
	use IController;
	use Model\Session;
	use APIException;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetSession extends APIPublicMethod {

		/** @var string */
		protected $authKey;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `authorize` WHERE `authKey` = '%s' LIMIT 1", $this->authKey);

			$session = $db->query($sql, DatabaseResultType::ITEM);

			if (!$session) {
				throw new APIException(ERROR_SESSION_NOT_FOUND);
			}

			return new Session($session);
		}
	}