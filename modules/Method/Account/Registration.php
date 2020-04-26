<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Регистрация пользователя
	 * @package Method\Account
	 */
	class Registration extends APIPublicMethod {

		use TCheckSexRange;

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

		/** @var string */
		protected $captchaId;

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

			/// In react 201
			$secret = GOOGLE_RECAPTCHA_SECRET_TOKEN_V2;
			if (API_VERSION < 201) {
				$secret = GOOGLE_RECAPTCHA_SECRET_TOKEN;
			}
			///

			$grc = check_recaptcha_v3($this->captchaId, $secret);
			if (!$grc->success) {
				throw new APIException(ErrorCode::CAPTCHA_FAILED, $grc, "Captcha check failed");
			}

			if (isset($grc->score) && $grc->score < 0.6) {
				throw new APIException(ErrorCode::CAPTCHA_LOW_SCORE, [
					"score" => $grc->score
				], "Maybe, you are robot?");
			}

			$this->login = mb_strtolower($this->login);
			$this->email = mb_strtolower($this->email);

			$passLength = mb_strlen($this->password);

			if ($passLength < 6 || $passLength > 32) {
				throw new APIException(ErrorCode::INCORRECT_LENGTH_PASSWORD, null, "Password must be length between 6 and 32 symbols");
			}

			if (!preg_match("/^([A-Za-z][A-Za-z_0-9.-]+)$/iu", $this->login)) {
				throw new APIException(ErrorCode::RESTRICTED_SYMBOLS_IN_LOGIN, null, "Restricted symbols in login");
			}

			if (mb_strlen($this->firstName) < 2 || mb_strlen($this->lastName) < 2 || !inRange(mb_strlen($this->login), 4, 20)) {
				throw new APIException(ErrorCode::INCORRECT_NAMES, null, "Name and last name must be 2 or more symbols, login must be between 4 and 20 symbols");
			}

			if (!$this->isSexInRange($this->sex)) {
				throw new APIException(ErrorCode::INVALID_SEX, null, "Sex value is invalid");
			}

			if (!isValidEmail($this->email)) {
				throw new APIException(ErrorCode::INVALID_EMAIL, null, "Invalid format email");
			}

			if (!$main->perform(new IsFreeLogin(["login" => $this->login]))) {
				throw new APIException(ErrorCode::LOGIN_ALREADY_EXIST, null, "Login already exists");
			}

			if (!$main->perform(new IsFreeEmail(["email" => $this->email]))) {
				throw new APIException(ErrorCode::EMAIL_ALREADY_EXIST, null, "This email already used");
			}

			$passwordHash = $main->perform(new GetPasswordHash(["password" => $this->password]));

			$sql = $main->makeRequest("INSERT INTO `user` (`firstName`, `lastName`, `login`, `email`, `password`, `sex`, `cityId`, `vkId`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([$this->firstName, $this->lastName, $this->login, $this->email, $passwordHash, $this->sex, $this->cityId > 0 ? $this->cityId : null, $this->vkId > 0 ? $this->vkId : null]);

			$userId = (int) $main->getDatabaseProvider()->lastInsertId();

			$hash = substr(md5($userId . (time() & rand(0, time() / 2048))), 0, 10);

			$stmt = $main->makeRequest("INSERT INTO `activate` (`userId`, `hash`) VALUES (?, ?)");
			$stmt->execute([$userId, $hash]);

			$text = sprintf("Для активации аккаунта, пожалуйста, перейдите по ссылке\r\nhttp://%s/userarea/activation?hash=%s%s\r\n\r\nИли вставьте код, если Вы регистрируетесь через новую форму: %s", DOMAIN_MAIN, $hash, API_VERSION >= 250 ? "&new=1" : '', $hash);

			send_mail($this->email, "Активация аккаунта на сайте Sights Map", "Активация аккаунта", $text, true);

			return ["result" => true, "userId" => $userId];
		}
	}