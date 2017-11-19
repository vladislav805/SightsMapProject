<?php

	namespace Method\User;

	use Model\IController;
	use Method\APIException;
	use Method\APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Registration extends APIPublicMethod {

		/** @var string */
		protected $login;

		/** @var string */
		protected $password;

		/** @var string */
		protected $firstName;

		/** @var string */
		protected $lastName;

		/** @var string */
		protected $sex;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$main->perform(new IsFreeLogin(["login" => $this->login]))) {
				throw new APIException(ERROR_LOGIN_ALREADY_EXIST);
			}

			$this->login = mb_strtolower($this->login);

			$passLength = mb_strlen($this->password);

			if ($passLength < 6 || $passLength > 32) {
				throw new APIException(ERROR_INCORRECT_LENGTH_PASSWORD);
			}

			if (mb_strlen($this->firstName) < 2 || mb_strlen($this->lastName) < 2) {
				throw new APIException(ERROR_INCORRECT_NAMES);
			}

			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$sql = sprintf("INSERT INTO `user` (`firstName`, `lastName`, `login`, `password`, `sex`) VALUES ('%s', '%s', '%s', '%s', '%d')", $this->firstName, $this->lastName, $this->login, $passwordHash, $this->sex);

			$userId = $db->query($sql, DatabaseResultType::INSERTED_ID);

			return ["result" => true, "userId" => $userId];
		}
	}