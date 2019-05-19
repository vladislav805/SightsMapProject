<?php

	namespace Method\Authorize;

	use Method\Account\GetPasswordHash;
	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Session;
	use Model\User;
	use PDO;

	/**
	 * Прямая авторизация
	 * @package Method\Authorize
	 */
	class DirectAuthorize extends APIPublicMethod {

		/** @var string */
		protected $login;

		/** @var string */
		protected $password;

		/**
		 * @param IController $main
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$this->login = mb_strtolower($this->login);

			$stmt = $main->makeRequest("SELECT * FROM `user` WHERE (`email` = :l OR `login` = :l) AND `password` = :p LIMIT 1");
			$stmt->execute([":l" => $this->login, ":p" => $passwordHash]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$result) {
				throw new APIException(ErrorCode::INCORRECT_LOGIN_PASSWORD, null, "Invalid login/password pair");
			}

			$user = new User($result);

			if (!$user->getId()) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, null, "Unknown error: userId is null");
			}

			if ($user->getStatus() === User::STATE_INACTIVE) {
				throw new APIException(ErrorCode::ACCOUNT_NOT_ACTIVE);
			}

			return $main->perform(new CreateSession(["userId" => $user->getId(), "access" => -1]));
		}
	}