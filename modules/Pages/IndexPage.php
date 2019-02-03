<?

	namespace Pages;

	use Method\APIException;

	class IndexPage extends BasePage implements RibbonPage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Sights";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/index.js");

			$this->addClassBody("index--body");
		}

		public function getJavaScriptInit($data) {
			return "Index.init();";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "Неформальные достопримечательности";
		}

		/**
		 * @param mixed $data
		 * @return string|null
		 */
		public function getRibbonImage($data) {
			return null;
		}

		/**
		 * @param mixed $data
		 * @throws APIException
		 */
		public function getRibbonContent($data) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$counts = $this->mController->perform(new \Method\Sight\GetCounts([]));
			require_once self::$ROOT_DOC_DIR . "index.ribbon.php";
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "index.content.php";
		}
	}