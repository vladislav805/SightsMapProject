<?

	namespace Pages;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\ErrorCode;
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
					try {
						$res = $this->mController->perform(new \Method\Authorize\Authorize(["login" => $login, "password" => $password]));
					} catch (APIException $e) {
						// TODO: design
						switch ($e->getCode()) {
							case ErrorCode::ACCOUNT_NOT_ACTIVE:
								print "Account not active. Please, follow link sent to specified email.";
								exit;

							case ErrorCode::INCORRECT_LOGIN_PASSWORD:
								print "Invalid pair login/password.";
								exit;

							default:
								printf("Unknown error: 0x%x", $e->getCode());
								exit;
						}
					}

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