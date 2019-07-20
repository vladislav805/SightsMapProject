<?php

	namespace Method\Authorize;

	use Credis_Client;
	use Method\Account\IsFreeLogin;
	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\User;
	use PDO;

	/**
	 * Авторизация через ВКонтакте
	 * @package Method\Authorize
	 */
	class AuthorizeViaVk extends APIPublicMethod {

		/** @var string */
		protected $code;

		/** @var string */
		protected $error;

		/** @var string */
		protected $login;

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if (!$this->code) {
				throw new APIException(ErrorCode::NO_PARAM, null, "code is not specified");
			}

			if ($this->error) {
				throw new APIException(ErrorCode::AUTHORIZE_VK_FAILED, null, sprintf("%s - %s", get("error"), get("error_description")));
			}

			list($accessToken, $vkUserId) = $this->getToken($main->getRedis(), $this->code);

			$userId = $this->findUserByVkId($main, $vkUserId);

			if (!$userId) {
				$vkUserInfo = $this->getVkUserInfo($accessToken, $vkUserId);
				$userId = $this->registerNew($main, $vkUserInfo);
			}

			return $main->perform(new CreateSession(["userId" => $userId]));
		}

		/**
		 * @param Credis_Client $redis
		 * @param string $code
		 * @return string[]
		 */
		private function getToken($redis, $code) {
			$rKey = "avvk_" . $code;
			if ($redis->get($rKey)) {
				return json_decode($redis->get($rKey));
			}

			$url = sprintf("https://oauth.vk.com/access_token?client_id=%d&client_secret=%s&redirect_uri=%s&code=%s", VK_CLIENT_ID, VK_CLIENT_SECRET, VK_REDIRECT_URL_V2, $code);

			$data = json_decode(file_get_contents($url));
			$res = [$data->access_token, $data->user_id];

			$redis->set($rKey, json_encode($res, JSON_UNESCAPED_UNICODE), 15 * MINUTE);

			return $res;
		}

		/**
		 * @param IController $main
		 * @param int $vkUserId
		 * @return int
		 */
		private function findUserByVkId(IController $main, $vkUserId) {
			$stmt = $main->makeRequest("SELECT `userId` FROM `user` WHERE `vkId` = :vkId LIMIT 1");
			$stmt->execute([":vkId" => $vkUserId]);

			list($userId) = $stmt->fetch(PDO::FETCH_NUM);
			return (int) $userId;
		}

		/**
		 * @param string $token
		 * @param int $vkUserId
		 * @return object
		 */
		private function getVkUserInfo($token, $vkUserId) {
			$url = sprintf("https://api.vk.com/method/users.get?user_ids=%d&access_token=%s&fields=sex,screen_name&v=5.104&lang=ru", $vkUserId, $token);

			$data = json_decode(file_get_contents($url));

			return $data->response[0];
		}

		/**
		 * @param IController $main
		 * @param object $vkUser
		 * @return int
		 */
		private function registerNew(IController $main, $vkUser) {

			$this->login = mb_strtolower($this->login);

			if (!$this->login) {
				$this->login = $vkUser->screen_name;
			}

			if (!$main->perform(new IsFreeLogin(["login" => $this->login]))) {
				throw new APIException(ErrorCode::LOGIN_ALREADY_EXIST);
			}

			if (!preg_match("/^([A-Za-z][A-Za-z_0-9.-]+)$/iu", $this->login)) {
				throw new APIException(ErrorCode::RESTRICTED_SYMBOLS_IN_LOGIN, null, "Restricted symbols in login");
			}

			if (!inRange(mb_strlen($this->login), 4, 20)) {
				throw new APIException(ErrorCode::INCORRECT_NAMES, null, "Login must be between 4 and 20 symbols");
			}

			$sql = $main->makeRequest("INSERT INTO `user` (`firstName`, `lastName`, `login`, `sex`, `vkId`, `status`) VALUES (?, ?, ?, ?, ?, ?)");
			$sql->execute([
				$vkUser->first_name,
				$vkUser->last_name,
				$this->login,
				[User::GENDER_NOT_SET, User::GENDER_FEMALE, User::GENDER_MALE][$vkUser->sex],
				$vkUser->id,
				User::STATE_USER
			]);

			$userId = (int) $main->getDatabaseProvider()->lastInsertId();

			return $userId;
		}

	}