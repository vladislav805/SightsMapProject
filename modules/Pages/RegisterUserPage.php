<?

	namespace Pages;

	use Model\ListCount;

	class RegisterUserPage extends BasePage {

		private $isEdit = false;

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return $this->isEdit ? "Редактирование профиля" : "Регистрация | Sights";
		}

		public function getPageTitle($data) {
			return $this->isEdit ? "Редактирование профиля" : "Регистрация нового пользователя";
		}

		/**
		 * @param string|null $action
		 * @return array
		 */
		protected function prepare($action) {

			$hasSession = $this->mController->getSession() !== null;

			if ($action === "edit" && !$hasSession) {
				redirectTo("/login");
			}

			if ($action === "create" && $hasSession) {
				redirectTo("/");
			}

			$this->isEdit = $hasSession;

			$user = $this->isEdit ? $this->mController->perform(new \Method\User\GetById(["extra" => "photo,city"])) : null;

			$this->addScript("/pages/js/api.js");

			if (!$hasSession) {
				$this->addScript("/pages/js/register-page.js");
			} else {
				$this->addScript("/pages/js/userarea-page.js");
				$this->addScript("/pages/js/ui/modal.js");
			}

			/** @var ListCount $cities */
			$cities = $this->mController->perform(new \Method\City\Get([]));

			$cities = \Utils\generateCitiesTree($cities->getItems());
			if ($user && $user->getCity()) {
				unset($cities[0]["selected"]);
				foreach ($cities as &$city) {
					if ($city["value"] === $user->getCity()->getId()) {
						$city["selected"] = true;
						break;
					}
				}
			}

			return ["user" => $user, "cities" => $cities];
		}

		public function getJavaScriptInit($data) {
			return $this->mController->getSession() !== null ? "onReady(() => UserArea.onReady());" : null;
		}

		public function getContent($data) {
			$user = $data["user"] ?? null;
			$cities = $data["cities"] ?? null;

			require_once parent::$ROOT_DOC_DIR . "registration.content.php";
			if ($user) {
				require_once parent::$ROOT_DOC_DIR . "userarea.editInfo.content.php";
			}
		}

	}