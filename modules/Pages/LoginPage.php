<?

	namespace Pages;

	use InvalidArgumentException;
	use Method\APIException;
	use Model\User;

	class LoginPage extends BasePage {

		/**
		 * @return string
		 */
		public function getBrowserTitle() {
			return "Авторизация | Sights";
		}

		public function getPageTitle() {
			return "Login";
		}

		/**
		 * @param string|null $action
		 * @throws APIException
		 */
		protected function prepare($action) {
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
					redirectTo("/user/" . $user->getLogin());
					exit;
				} else {
					throw new InvalidArgumentException("Login/password not specified");
				}
			}

			if ($action === "logout") {
				setCookie(KEY_TOKEN, null, 1, "/");
				redirectTo("/index");
				exit;
			}

			if ($this->mController->getSession()) {
				redirectTo("/index");
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