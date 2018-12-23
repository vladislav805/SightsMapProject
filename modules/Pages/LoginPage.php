<?

	namespace Pages;

	use InvalidArgumentException;
	use Method\APIException;
	use Model\User;

	class LoginPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Авторизация | Sights";
		}

		public function getPageTitle($data) {
			return "Login";
		}

		/**
		 * @param string|null $action
		 * @throws APIException
		 */
		protected function prepare($action) {
			$repath = get("repath");

			if ($action === "authorize") {
				$login = get("login");
				$password = get("password");

				if ($login && $password) {
					$res = $this->mController->perform(new \Method\Authorize\Authorize(["login" => $login, "password" => $password]));

					/** @var string $authKey */
					$authKey = $res["authKey"];

					/** @var User $user */
					$user = $res["user"];

					setCookie(KEY_TOKEN, $authKey, strtotime("+30 days"), "/");

					if (!$repath) {
						$repath = "/user/" . $user->getLogin();
					}

					redirectTo($repath);
					exit;
				} else {
					throw new InvalidArgumentException("Login/password not specified");
				}
			}

			if ($action === "logout") {
				setCookie(KEY_TOKEN, null, 1, "/");
				redirectTo($repath ?? "/");
				exit;
			}

			if ($this->mController->getSession()) {
				redirectTo($repath ?? "/");
				exit;
			}
		}

		protected function getTemplateUriTop() {
			return parent::$ROOT_DOC_DIR . "login.top.php";
		}

		protected function getTemplateUriHeader() {
			return null;
		}

		protected function getTemplateUriFooter() {
			return null;
		}

		protected function getTemplateUriBottom() {
			return parent::$ROOT_DOC_DIR . "login.bottom.php";
		}

		public function getContent($data) {
			require_once parent::$ROOT_DOC_DIR . "login.content.php";
		}

	}