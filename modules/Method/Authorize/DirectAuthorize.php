<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Method\User\GetPasswordHash;
	use Model\Session;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param DatabaseConnection $db
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$this->login = mb_strtolower($this->login);

			$stmt = $main->makeRequest("SELECT `userId` FROM `user` WHERE (`email` = :l OR `login` = :l) AND `password` = :p LIMIT 1");
			$stmt->execute([":l" => $this->login, ":p" => $passwordHash]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			$userId = (int) $result["userId"];

			if (!$result || !$userId) {
				throw new APIException(ERROR_INCORRECT_LOGIN_PASSWORD);
			};

			$access = -1;

			return $main->perform(new CreateSession(["userId" => $userId, "access" => $access]));
		}
	}