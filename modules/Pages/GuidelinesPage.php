<?
	/** @noinspection PhpUnusedParameterInspection */

	namespace Pages;

	class GuidelinesPage extends BasePage {

		/**
		 * @param $action
		 * @return mixed
		 */
		protected function prepare($action) {
			return null;
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Гайдлайны сайта";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "guidelines.php";
		}

	}