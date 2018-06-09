<?php

	namespace Method\User;

	use Model\IController;
	use Method\APIException;
	use Method\APIPublicMethod;

	/**
	 * Регистрация пользователя
	 * @package Method\User
	 */
	class Registration extends APIPublicMethod {

		/** @var string */
		protected $login;

		/** @var string */
		protected $email;

		/** @var string */
		protected $password;

		/** @var string */
		protected $firstName;

		/** @var string */
		protected $lastName;

		/** @var string */
		protected $sex;

		/** @var int */
		protected $cityId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($main->isAuthorized()) {
				throw new APIException(ERROR_ACCESS_DENIED);
			}

			$this->login = mb_strtolower($this->login);
			$this->email = mb_strtolower($this->email);

			$passLength = mb_strlen($this->password);

			if ($passLength < 6 || $passLength > 32) {
				throw new APIException(ERROR_INCORRECT_LENGTH_PASSWORD);
			}

			if (mb_strlen($this->firstName) < 2 || mb_strlen($this->lastName) < 2 || !inRange(mb_strlen($this->login), 4, 20)) {
				throw new APIException(ERROR_INCORRECT_NAMES);
			}

			if (!$main->perform(new IsFreeLogin(["login" => $this->login]))) {
				throw new APIException(ERROR_LOGIN_ALREADY_EXIST);
			}

			if (!$main->perform(new IsFreeEmail(["email" => $this->email]))) {
				throw new APIException(ERROR_EMAIL_ALREADY_EXIST);
			}

			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$sql = $main->makeRequest("INSERT INTO `user` (`firstName`, `lastName`, `login`, `email`, `password`, `sex`, `cityId`) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([$this->firstName, $this->lastName, $this->login, $this->email, $passwordHash, $this->sex, $this->cityId > 0 ? $this->cityId : null]);

			$userId = $main->getDatabaseProvider()->lastInsertId();

			return ["result" => true, "userId" => $userId];
		}
	}