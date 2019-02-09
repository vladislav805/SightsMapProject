<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Model\IItem;
	use Model\ListCount;

	class FeedPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "События | Sights Map";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/events-page.js");

			$feed = $this->mController->perform(new \Method\Event\Get(["extra" => "photo"]));

			return $feed;
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "События";
		}

		/**
		 * @param ListCount $data
		 * @return void
		 */
		public function getContent($data) {

			$items = $data->getItems();
			$sights = $this->makeMap($data->getCustomData("sights"));
			$users = $this->makeMap($data->getCustomData("users"));
			$photos = $this->makeMap($data->getCustomData("photos"));

			require_once self::$ROOT_DOC_DIR . "feed.content.php";
		}

		/**
		 * @param IItem[] $items
		 * @return array
		 */
		private function makeMap($items) {
			$k = [];

			foreach ($items as $item) {
				$k[$item->getId()] = $item;
			}

			return $k;
		}

	}