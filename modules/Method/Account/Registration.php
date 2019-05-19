<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use tools\PHPMailer\Exception;
	use tools\PHPMailer\PHPMailer;

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

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if ($main->isAuthorized()) {
				throw new APIException(ErrorCode::ACCESS_DENIED);
			}

			$this->login = mb_strtolower($this->login);
			$this->email = mb_strtolower($this->email);

			$passLength = mb_strlen($this->password);

			if ($passLength < 6 || $passLength > 32) {
				throw new APIException(ErrorCode::INCORRECT_LENGTH_PASSWORD, null, "Password must be length between 6 and 32 symbols");
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

			$sql = $main->makeRequest("INSERT INTO `user` (`firstName`, `lastName`, `login`, `email`, `password`, `sex`, `cityId`) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$sql->execute([$this->firstName, $this->lastName, $this->login, $this->email, $passwordHash, $this->sex, $this->cityId > 0 ? $this->cityId : null]);

			$userId = (int) $main->getDatabaseProvider()->lastInsertId();

			$hash = md5($userId . (time() & rand(0, time() / 2048)));

			$stmt = $main->makeRequest("INSERT INTO `activate` (`userId`, `hash`) VALUES (?, ?)");
			$stmt->execute([$userId, $hash]);

			$text = sprintf("Для активации аккаунта, пожалуйста, перейдите по ссылке\r\nhttp://%s/userarea/activation?hash=%s", DOMAIN_MAIN, $hash);

			$mail = new PHPMailer(true);

			try {
				$mail->SMTPDebug = 2;
				$mail->Debugoutput = "error_log";
				$mail->isSMTP();
				$mail->Host = EMAIL_HOST;
				$mail->SMTPAuth = true;
				$mail->Username = EMAIL_LOGIN;
				$mail->Password = EMAIL_PASSWORD;
				$mail->SMTPSecure = EMAIL_SECURE;
				$mail->Port = EMAIL_PORT;
				$mail->CharSet = "utf-8";

				$mail->setFrom(EMAIL_LOGIN, "No reply");
				$mail->addAddress($this->email);
				//$mail->isHTML(true);
				$mail->Subject = "Активация аккаунта на сайте Sights Map";
				$mail->Body    = $text;
				//$mail->AltBody = $text;

				$mail->send();
			} catch (Exception $e) {
				echo $mail->ErrorInfo;
				exit;
			}

			return ["result" => true, "userId" => $userId];
		}
	}