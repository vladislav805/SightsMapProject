<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Method\User\GetPasswordHash;
	use Model\Session;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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

			$sql = sprintf("SELECT `userId` FROM `user` WHERE `login` = '%1\$s' AND `password` = '%2\$s' LIMIT 1", $this->login, $passwordHash);

			$result = $db->query($sql, DatabaseResultType::ITEM);

			$userId = (int) $result["userId"];

			if (!$result || !$userId) {
				throw new APIException(ERROR_INCORRECT_LOGIN_PASSWORD);
			};

			$access = Access::WRITE_INFO | Access::READ_MAP | Access::READ_MAP | Access::WRITE_MAP | Access::WRITE_USER_STATUS | Access::READ_COMMENTS | Access::WRITE_COMMENTS | ACCESS::READ_PHOTOS | Access::WRITE_PHOTOS;

			return $main->perform(new CreateSession(["userId" => $userId, "access" => $access]));
		}
	}