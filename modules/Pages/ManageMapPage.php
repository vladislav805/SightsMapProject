<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Method\Sight\GetById;
	use Model\Params;
	use Model\Sight;

	class ManageMapPage extends BasePage implements WithBackLinkPage {

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
			$this->addScript("/pages/js/ui/toast.js");
			$this->addScript("/pages/js/ui/smart-modals.js");
			$this->addStylesheet("/css/map.css");

			$this->mClassBody = "page--manageMap";

			$sight = null;
			$photos = ["items" => [], "users" => []];

			if (($sightId = get("id")) && is_numeric($sightId)) {
				$args = (new Params)->set("sightId", $sightId);
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

			$cities = \Utils\generateCitiesTree($cities);

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

		private function getJavaScriptObject($data) {
			list($sight, $marks, $cities, $photos) = $data;

			$res = [
				"sight" => $sight,
				"photos" => $photos["items"]
			];

			return $res;
		}

		public function getBackURL($data) {
			/** @var Sight $sight */
			list($sight) = $data;

			if (!$sight) {
				return null;
			}

			return "/sight/" . $sight->getId();
		}

	}