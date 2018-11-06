<?

	namespace Pages;

	class RegisterUserPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Регистрация | Sights";
		}

		public function getPageTitle($data) {
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

			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/register-page.js");
		}

		public function getContent($data) {
			require_once parent::$ROOT_DOC_DIR . "registration.content.php";
		}

	}