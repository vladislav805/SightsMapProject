<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Method\NeuralNetwork\CheckErrorNetwork;
	use Method\NeuralNetwork\IsNetworkExists;
	use Model\ListCount;

	class NeuralPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Нейросеть | Sights Map";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/common-map.js");
			$this->addScript("//api-maps.yandex.ru/2.1/?lang=ru_RU");
			$this->addScript("/pages/js/neural-page.js");
			$this->addScript("/pages/js/ui/modal.js");

			$has = false;
			$error = null;

			if ($this->mController->isAuthorized()) {
				$has = $this->mController->perform(new IsNetworkExists([]));
				$error = $this->mController->perform(new CheckErrorNetwork([]));
			}

			return [$error, $has];
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "Нейросеть";
		}

		/**
		 * @param array $d
		 * @return void
		 */
		public function getContent($d) {
			/** @var ListCount $data */
			list($error, $has) = $d;

			require_once self::$ROOT_DOC_DIR . "neural.content.php";
		}

		public function getJavaScriptInit($data) {
			list($error, $has) = $data;
			$hasStr = ((int) $has);
			return "onReady(() => NeuralPage.init({has: $hasStr}));";
		}

	}