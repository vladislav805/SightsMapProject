<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Session;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class CreateSession extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/** @var int */
		protected $access;

		/**
		 *
		 * @param array $request
		 */
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
			$authKey = $main->perform(new CreateAuthKey(["userId" => $this->userId]));

			$sql = sprintf("INSERT INTO `authorize` (`authKey`, `userId`, `accessMask`, `date`) VALUES ('%s', '%d', '%d', UNIX_TIMESTAMP(NOW()))", $authKey, $this->userId, $this->access);

			$authId = $db->query($sql, DatabaseResultType::INSERTED_ID);

			if (!$authId) {
				throw new APIException(ERROR_UNKNOWN_ERROR);
			}

			return $main->perform(new GetSession(["authKey" => $authKey]));
		}
	}