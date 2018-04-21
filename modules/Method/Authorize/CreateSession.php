<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Session;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	/**
	 * Регистрация сессии
	 * @package Method\Authorize
	 */
	class CreateSession extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/** @var int */
		protected $access;

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

			$sql = "INSERT INTO `authorize` (`authKey`, `userId`, `accessMask`, `date`) VALUES (?, ?, ?, UNIX_TIMESTAMP(NOW()))";
			$stmt = $main->makeRequest($sql);
			$stmt->execute([$authKey, $this->userId, $this->access]);

			$authId = $main->getDatabaseProvider()->lastInsertId();

			if (!$authId) {
				throw new APIException(ERROR_UNKNOWN_ERROR);
			}

			return new Session([
				"authId" => $authId,
				"authKey" => $authKey,
				"userId" => $this->userId,
				"date" => time(),
				"access" => $this->access
			]);
		}
	}