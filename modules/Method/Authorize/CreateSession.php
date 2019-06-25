<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Session;

	/**
	 * Регистрация сессии
	 * @package Method\Authorize
	 */
	class CreateSession extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/**
		 * @param IController $main
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$authKey = $main->perform(new CreateAuthKey(["userId" => $this->userId]));

			$sql = "INSERT INTO `authorize` (`authKey`, `userId`, `date`) VALUES (?, ?, UNIX_TIMESTAMP(NOW()))";
			$stmt = $main->makeRequest($sql);
			$stmt->execute([$authKey, $this->userId]);

			if (!$stmt->rowCount()) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, $stmt->errorInfo(), "Unknown error");
			}

			$authId = $main->getDatabaseProvider()->lastInsertId();

			if (!$authId) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, null, "Unknown error while write session");
			}

			return new Session([
				"authId" => $authId,
				"authKey" => $authKey,
				"userId" => $this->userId,
				"date" => time()
			]);
		}
	}