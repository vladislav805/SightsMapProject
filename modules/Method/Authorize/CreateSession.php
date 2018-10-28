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

		/** @var int */
		protected $access;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$authKey = $main->perform(new CreateAuthKey(["userId" => $this->userId]));

			$sql = "INSERT INTO `authorize` (`authKey`, `userId`, `accessMask`, `date`) VALUES (?, ?, ?, UNIX_TIMESTAMP(NOW()))";
			$stmt = $main->makeRequest($sql);
			$stmt->execute([$authKey, $this->userId, $this->access]);

			$authId = $main->getDatabaseProvider()->lastInsertId();

			if (!$authId) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR);
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