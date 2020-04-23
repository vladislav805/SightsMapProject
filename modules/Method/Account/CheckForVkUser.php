<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\Authorize\AuthorizeVK;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Session;

	/**
	 * @package Method\Account
	 */
	class CheckForVkUser extends APIPublicMethod {

		/** @var string */
		protected $code;

		/** @var string */
		protected $debug; // TODO remove this after testing

		/**
		 * @param IController $main
		 * @return Session | array
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->code) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$redirectUri = !$this->debug
				? 'https://sights.velu.ga/island/vk'
				: 'http://0.0.0.0:8080/island/vk';

			list($token, $vkId) = $this->getToken($this->code, $redirectUri);

			/** @var Session | null $res */
			$res = $main->perform(new AuthorizeVK(["vkId" => $vkId]));

			if ($res) {
				return ["session" => $res];
			}

			$user = $this->getInfo($token, $vkId);

			return [
				"session" => null,
				"current" => [
					"vkId" => $user->id,
					"firstName" => $user->first_name,
					"lastName" => $user->last_name,
					"login" => $user->screen_name,
					"sex" => $user->sex,
				]
			];
		}

		/**
		 * @param string $code
		 * @param string $redirectUri
		 * @return array
		 */
		private function getToken($code, $redirectUri) {
			$url = sprintf("https://oauth.vk.com/access_token?client_id=%d&client_secret=%s&redirect_uri=%s&code=%s", VK_CLIENT_ID, VK_CLIENT_SECRET, $redirectUri, $code);

			$data = json_decode(file_get_contents($url));

			return [$data->access_token, $data->user_id];
		}

		/**
		 * @param string $token
		 * @param int $vkId
		 * @return object
		 */
		private function getInfo($token, $vkId) {
			$url = sprintf("https://api.vk.com/method/execute.getInfo?user_ids=%d&access_token=%s&fields=sex,screen_name&v=5.103&lang=ru", $vkId, $token);

			$data = json_decode(file_get_contents($url));

			return $data->response;
		}
	}
