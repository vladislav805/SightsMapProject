<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Constant\VisitState;
	use Model\IItem;
	use Model\ListCount;
	use Model\Sight;

	class NeuralPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Тестовая нейронка | Sights Map";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/common-map.js");

			$data = false;
			if ($this->mController->isAuthorized()) {
				$data = $this->mController->perform(new \Method\NeuralNetwork\GetInterestedSights([]));
			}

			return $data;
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "Тестовая нейронка";
		}

		/**
		 * @param ListCount $data
		 * @return void
		 */
		public function getContent($data) {
			if ($data) {
				$sights = $this->makeMap($data->getItems());
			}

			require_once self::$ROOT_DOC_DIR . "neural.content.php";
		}

		/**
		 * @param Sight $sight
		 * @return string
		 */
		private function getClasses(Sight $sight) {
			$cls = [];

			if ($sight->isVerified()) {
				$cls[] = "search-item--verified";
			}

			if ($sight->isArchived()) {
				$cls[] = "search-item--archived";
			}

			if ($sight->getVisitState() === VisitState::VISITED) {
				$cls[] = "search-item--visited";
			}

			if ($sight->getVisitState() === VisitState::DESIRED) {
				$cls[] = "search-item--desired";
			}

			return join(" ", $cls);
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