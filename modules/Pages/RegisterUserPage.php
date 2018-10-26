<?

	namespace Pages;

	use InvalidArgumentException;
	use Method\APIException;
	use Model\User;

	class RegisterUserPage extends BasePage {

		/**
		 * @return string
		 */
		public function getBrowserTitle() {
			return "Регистрация | Sights";
		}

		public function getPageTitle() {
			return "Регистрация нового пользователя";
		}

		/**
		 * @param string|null $action
		 */
		protected function prepare($action) {
			if ($this->mController->getSession()) {
				redirectTo("/index");
				exit;
			}
		}

		public function getContent($data) {
			require_once parent::$ROOT_DOC_DIR . "registration.content.php";
		}

	}