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
			$this->addScript("/js/api.js");
			$this->addScript("/pages/js/index.js");
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
			$counts = $this->mController->perform(new \Method\Point\GetCounts([]));
			require_once self::$ROOT_DOC_DIR . "index.ribbon.php";
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "index.content.php";
		}
	}