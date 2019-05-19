<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\User;

	/**
	 * Регистрация пользователя через ВК
	 * @package Method\User
	 */
	class RegistrationVK extends APIPublicMethod {

		/** @var string */
		protected $login;

		/** @var string */
		protected $email;

		/** @var string */
		protected $firstName;

		/** @var string */
		protected $lastName;

		/** @var string */
		protected $sex;

		/** @var int */
		protected $vkId;

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if ($main->isAuthorized()) {
				throw new APIException(ErrorCode::ACCESS_DENIED);
			}

			if (!$this->vkId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "vkId is missed");
			}

			$this->login = mb_strtolower($this->login);
			$this->email = mb_strtolower($this->email);

			if (!isValidEmail($this->email) || !$main->perform(new IsFreeEmail(["email" => $this->email])) || !strlen($this->email)) {
				$this->email = null;
			}

			$sql = $main->makeRequest("INSERT INTO `user` (`firstName`, `lastName`, `login`, `email`, `sex`, `vkId`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([$this->firstName, $this->lastName, $this->login, $this->email, $this->sex, $this->vkId, User::STATE_USER]);

			$userId = (int) $main->getDatabaseProvider()->lastInsertId();

			return ["result" => true, "userId" => $userId, "error" => $sql->errorInfo()];
		}
	}