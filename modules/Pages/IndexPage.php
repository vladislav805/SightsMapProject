<?

	namespace Pages;

	class IndexPage extends BasePage implements RibbonPage {

		/**
		 * @return string
		 */
		public function getBrowserTitle() {
			return "Sights";
		}

		protected function prepare() {
			$this->addStylesheet("/css/pizda.css");
			return "pizda";
		}

		/**
		 * @return string
		 */
		public function getPageTitle() {
			return "Index";
		}

		public function getContent($data) {
			print $data;
		}

		public function getRibbonImage() {
			return "https://sights.vlad805.ru/userdata/dc22fe292db3/76ff021ba3e0/5fd808174999/810b8f7c3dfb.b.jpg";
		}

		public function getRibbonContent() {
			return "Title of sight";
		}
	}