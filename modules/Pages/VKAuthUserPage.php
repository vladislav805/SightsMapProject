<?

	namespace Pages;

	use Method\APIException;
	use Method\Authorize\AuthorizeVK;
	use Method\User\RegistrationVK;
	use Model\Controller;
	use Model\Params;
	use Model\Session;

	class VKAuthUserPage extends BasePage implements VirtualPage {

		private $REDIRECT_URI;

		public function __construct(Controller $controller, string $dir) {
			parent::__construct($controller, $dir);
			$this->REDIRECT_URI = urlencode("https://" . DOMAIN_MAIN . "/userarea/vk");
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return null;
		}

		public function getPageTitle($data) {
			return null;
		}

		/**
		 * @param string|null $action
		 * @throws APIException
		 */
		protected function prepare($action) {
			$code = get("code");
			if (!$code) {
				redirectTo($this->getVkAuthUrl());
				exit;
			}

			if (get("error")) {
				print get("error") . " = " . get("error_description");
				exit;
			}

			list($token, $vkId) = $this->getToken($code);

			$res = $this->mController->perform(new AuthorizeVK((new Params)->set("vkId", $vkId)));

			if (!$res) {
				$user = $this->getInfo($token, $vkId);

				$p = (new Params)
					->set("firstName", $user->first_name)
					->set("lastName", $user->last_name)
					->set("sex", $user->sex)
					->set("login", $user->screen_name)
					->set("vkId", $vkId);

				if ($email = get("email")) {
					$p->set("email", $email);
				}

				try {
					$this->mController->perform(new RegistrationVK($p));
					$res = $this->mController->perform(new AuthorizeVK((new Params)->set("vkId", $vkId)));
				} catch (APIException $e) {
					print "Произошла ошибка: " . $e->getMessage();
					exit;
				}
			}

			/** @var Session $res */
			setCookie(KEY_TOKEN, $res->getAuthKey(), strtotime("+30 days"), "/");
			redirectTo("/user/" . $res->getUserId());
		}

		private function getVkAuthUrl() {
			return sprintf("https://oauth.vk.com/authorize?client_id=%d&display=page&redirect_uri=%s&scope=%s&response_type=code&v=%s", VK_CLIENT_ID, $this->REDIRECT_URI, "4194304", "5.90");
		}

		private function getToken($code) {
			$url = sprintf("https://oauth.vk.com/access_token?client_id=%d&client_secret=%s&redirect_uri=%s&code=%s", VK_CLIENT_ID, VK_CLIENT_SECRET, $this->REDIRECT_URI, $code);

			$data = json_decode(file_get_contents($url));

			return [$data->access_token, $data->user_id];
		}

		private function getInfo($token, $vkId) {
			$url = sprintf("https://api.vk.com/method/users.get?user_ids=%d&access_token=%s&fields=sex,screen_name&v=5.90&lang=ru", $vkId, $token);

			$data = json_decode(file_get_contents($url));

			return $data->response[0];
		}

		public function getContent($data) {
			return null;
		}

	}