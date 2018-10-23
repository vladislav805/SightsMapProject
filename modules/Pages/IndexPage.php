<?

	namespace Pages;

	use Method\APIException;

	class IndexPage extends BasePage implements RibbonPage {

		/**
		 * @return string
		 */
		public function getBrowserTitle() {
			return "Sights";
		}

		protected function prepare($action) {
			$this->addScript("/js/api.js");
			$this->addScript("/pages/js/index.js");
		}

		/**
		 * @return string
		 */
		public function getPageTitle() {
			return "Неформальные достопримечательности";
		}

		/**
		 * @return string|null
		 */
		public function getRibbonImage() {
			return null;
		}

		/**
		 * @throws APIException
		 */
		public function getRibbonContent() {
			$counts = $this->mController->perform(new \Method\Point\GetCounts([]));
			require_once self::$ROOT_DOC_DIR . "index.ribbon.php";
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "index.content.php";
		}
	}