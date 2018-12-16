<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Method\Sight\GetById;
	use Model\City;
	use Model\Params;
	use Model\Sight;

	class ManageMapPage extends BasePage {

		/**
		 * @param string $action
		 * @return array
		 * @throws \Method\APIException
		 */
		protected function prepare($action) {
			$this->addScript("//api-maps.yandex.ru/2.1/?lang=ru_RU");
			$this->addScript("/pages/js/api.js");
			$this->addScript("/lib/exif-js.min.js");
			$this->addScript("/lib/sortable.min.js");
			$this->addScript("/pages/js/common-map.js");
			$this->addScript("/pages/js/map-manage.js");
			$this->addScript("/pages/js/ui/modal.js");
			$this->addStylesheet("/css/map.css");

			$this->mClassBody = "page--manageMap";

			$sight = null;
			$photos = ["items" => [], "users" => []];

			if (($pointId = get("id")) && is_numeric($pointId)) {
				$args = (new Params)->set("pointId", $pointId);
				$sight = $this->mController->perform(new GetById($args));
				$photos = $this->mController->perform(new \Method\Photo\Get($args));
			} else {
				$sight = null;
			}

			/** @var \Model\ListCount $marksList */
			$marksList = $this->mController->perform(new \Method\Mark\Get(new Params));

			/** @var \Model\ListCount $citiesList */
			$citiesList = $this->mController->perform(new \Method\City\Get(new Params));

			/** @var \Model\Mark[] $marks */
			$marks = $marksList->getItems();

			/** @var \Model\City[] $cities */
			$cities = $citiesList->getItems();

			return [$sight, $marks, $cities, $photos];
		}

		public function getJavaScriptInit($data) {
			return "onReady(() => ManageMap.init() && ManageMap.setInitialData(" . json_encode($this->getJavaScriptObject($data), JSON_UNESCAPED_UNICODE) . "));";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			/** @var Sight $sight */
			$sight = $data[0];
			return $sight ? "Редактирование места" : "Добавление места";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		/**
		 * @param mixed $data
		 * @return void
		 */
		public function getContent($data) {
			/** @var Sight $sight */
			list($sight, $marks, $cities, $photos) = $data;

			$cities = $this->generateCitiesTree($cities);

			if ($sight && $sight->getCity()) {
				unset($cities[0]["selected"]);
				foreach ($cities as &$city) {
					if ($city["value"] === $sight->getCity()->getId()) {
						$city["selected"] = true;
						break;
					}
				}
			}

			require_once self::$ROOT_DOC_DIR . "manage-map.content.php";
		}

		/**
		 * @param City[] $cities
		 * @return array
		 */
		private function generateCitiesTree($cities) {
			$output = [];
			$all = [];
			$dangling = [];

			foreach ($cities as $entry) {
				$id = $entry->getId();

				if (!$entry->getParentId()) {
					$all[$id] = $entry;
					$output[] = &$all[$id];
				} else {
					$dangling[$id] = $entry;
				}
			}

			while (sizeOf($dangling) > 0) {
				foreach ($dangling as $entry) {
					$id = $entry->getId();
					$pid = $entry->getParentId();

					if (isset($all[$pid])) {
						$all[$id] = $entry;
						$all[$pid]->addChild($all[$id]);
						unset($dangling[$entry->getId()]);
					}
				}
			}

			unset($all, $dangling);

			$options = [];
			$options[] = ["label" => "не выбран", "value" => 0, "selected" => true];
			foreach ($output as $item) {
				$options = array_merge($options, $this->getCityOption($item));
			}

			unset($output);

			return $options;
		}

		/**
		 * @param \Model\City $item
		 * @param int $level
		 * @return array
		 */
		private function getCityOption($item, $level = 0) {
			$items = [
				[
					"label" => str_repeat(" ", $level) . $item->getName(),
					"value" => $item->getId()
				]
			];

			if (sizeOf($item->getChildren())) {
				$children = $item->getChildren();
				foreach ($children as $child) {
					$items = array_merge($items, $this->getCityOption($child, $level + 1));
				}
			}

			return $items;
		}

		private function getJavaScriptObject($data) {
			list($sight, $marks, $cities, $photos) = $data;

			$res = [
				"sight" => $sight,
				"photos" => $photos["items"]
			];

			return $res;
		}
	}