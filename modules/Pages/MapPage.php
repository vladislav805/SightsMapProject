<?

	namespace Pages;

	class MapPage extends BasePage {

		protected function prepare($action) {

			$this->addClassBody("page--wide");
			$this->addScript("//api-maps.yandex.ru/2.1/?lang=ru_RU");
			$this->addScript("/pages/js/api.js");
			$this->addScript("/lib/exif-js.min.js");
			$this->addScript("/pages/js/common-map.js");
			$this->addScript("/pages/js/map-page.js");
			$this->addStylesheet("/css/map.css");
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Карта достопримечательностей";
		}

		public function getJavaScriptInit($data) {
			return "onReady(() => MapPage.init());";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "map.content.php";
		}
	}