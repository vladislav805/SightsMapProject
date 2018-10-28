<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Method\User\GetPasswordHash;
	use Model\Session;
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

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$this->login = mb_strtolower($this->login);

			$stmt = $main->makeRequest("SELECT `userId` FROM `user` WHERE (`email` = :l OR `login` = :l) AND `password` = :p LIMIT 1");
			$stmt->execute([":l" => $this->login, ":p" => $passwordHash]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			$userId = (int) $result["userId"];

			if (!$result || !$userId) {
				throw new APIException(ErrorCode::INCORRECT_LOGIN_PASSWORD, null, "Invalid login/password pair");
			};

			$access = -1;

			return $main->perform(new CreateSession(["userId" => $userId, "access" => $access]));
		}
	}