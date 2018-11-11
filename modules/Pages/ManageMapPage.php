<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Method\Point\GetById;
	use Model\City;
	use Model\Point;

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
			$this->addScript("/pages/js/common-map.js");
			$this->addScript("/pages/js/map-manage.js");
			$this->addStylesheet("/css/map.css");

			$sight = null;
			$photos = [];

			if ($pointId = get("pointId")) {
				$sight = $this->mController->perform(new GetById((new \Model\Params)->set("pointId", $pointId)));
			} else {
				$sight = new Point(null);
			}

			/** @var \Model\ListCount $marksList */
			$marksList = $this->mController->perform(new \Method\Mark\Get(new \Model\Params));

			/** @var \Model\ListCount $citiesList */
			$citiesList = $this->mController->perform(new \Method\City\Get(new \Model\Params));

			/** @var \Model\Mark[] $marks */
			$marks = $marksList->getItems();

			/** @var \Model\City[] $cities */
			$cities = $citiesList->getItems();

			return [$sight, $marks, $cities];
		}

		public function getJavaScriptInit($data) {
			/** @var Point $sight */
			list($sight) = $data;
			return sprintf("ymaps.ready(function() { ManageMap.setInitialPositionPlacemark(%.8f, %.8f); });", $sight->getLat(), $sight->getLng());
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			/** @var Point $sight */
			$sight = $data[0];
			return $sight->getId() ? "Редактирование места" : "Добавление места";
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
			/**
			 * @var Point $sight
			 */
			list($sight, $marks, $cities) = $data;



			$cities = $this->generateCitiesTree($cities);

			if ($sight->getCity()) {
				unset($cities[0]["selected"]);
				foreach ($cities as &$city) {
					if ($city["value"] === $sight->getCity()->getId()) {
						$city["selected"] = true;
						break;
					}
				}
			}

			require_once self::$ROOT_DOC_DIR . "sight-add.content.php";
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
	}