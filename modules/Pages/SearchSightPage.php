<?
	/** @noinspection PhpUnusedParameterInspection,PhpUnusedPrivateMethodInspection,PhpFullyQualifiedNameUsageInspection */

	namespace Pages;

	use Constant\VisitState;
	use Model\City;
	use Model\ListCount;
	use Model\Mark;
	use Model\Params;
	use Model\Sight;
	use Throwable;

	class SearchSightPage extends BasePage implements RibbonPage, IncludeRibbonPage {

		/** @var string */
		protected $query;

		/** @var int[] */
		protected $markIds;

		/** @var int */
		protected $cityId;

		/** @var int */
		protected $page;

		/** @var int */
		protected $onlyVerified = 0;

		/** @var int */
		protected $onlyArchived = 0;

		/** @var int */
		protected $onlyWithPhotos = 0;

		/** @var string */
		protected $queryLower;

		/** @var City|null */
		protected $city;

		/** @var Mark[]|null */
		protected $marks;

		protected $count;
		protected $order = 0;

		protected $found = false;

		protected function prepare($action) {
			$this->query = get("query");
			$this->markIds = get("markIds");
			$this->cityId = (int) get("cityId");
			$this->page = (int) get("page");
			$this->order = (int) get("order");
			$this->onlyVerified = toRange((int) get("verified"), 0, 1);
			$this->onlyArchived = toRange((int) get("archived"), 0, 1);
			$this->onlyWithPhotos = toRange((int) get("photos"), 0, 1);
			$this->count = 50;

			if ($this->markIds) {
				$mid = explode(",", $this->markIds);
				$mid = array_map("intval", $mid);
				$mid = array_unique($mid);
				$mid = array_values(array_filter($mid));
				$this->markIds = $mid;
			} else {
				$this->markIds = [];
			}

			$this->queryLower = mb_strtolower($this->query);

			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/ui/modal.js");
			$this->addScript("/pages/js/ui/smart-modals.js");
			$this->addScript("/pages/js/search-page.js");
			$this->addScript("/pages/js/common-map.js");
			$this->addScript("//api-maps.yandex.ru/2.1/?lang=ru_RU");
			$result = null;

			$params = new Params;
			$params
				->set("offset", $this->count * $this->page)
				->set("count", $this->count);

			if (inRange($this->order, -2, 3)) {
				$params->set("order", $this->order);
			}

			if ($this->query) {
				$params->set("query", $this->query);
			}

			if ($this->markIds && sizeOf($this->markIds)) {
				$params->set("markIds", $this->markIds);
				$this->marks = $this->mController->perform(new \Method\Mark\GetById(["markIds" => $this->markIds]));
			}

			if ($this->cityId) {
				$params->set("cityId", $this->cityId);
				$city = $this->mController->perform(new \Method\City\GetById(["cityIds" => [$this->cityId]]));
				if (sizeOf($city)) {
					$this->city = $city[0];
				}
			}

			$this->onlyVerified && $params->set("isVerified", true);
			$this->onlyArchived && $params->set("isArchived", true);
			$this->onlyWithPhotos && $params->set("onlyWithPhotos", true);

			$result = null;

			try {
				/** @var ListCount $result */
				/** @noinspection PhpUnhandledExceptionInspection */
				$result = $this->mController->perform(new \Method\Sight\Search($params));

				$this->found = $result->getCount() > 0;

			} catch (Throwable $e) {
				$result = new ListCount(0, []);
			}

			return [$result];
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Поиск неформальных достопримечательностей";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		/**
		 * @param $data
		 * @return string
		 */
		public function getJavaScriptInit($data) {
			return "onReady(() => Search.init());";
		}

		public function getContent($data) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($result) = $data;
			require_once self::$ROOT_DOC_DIR . "search.sight.content.php";
		}

		/**
		 * @param Sight $item
		 */
		private function item($item) {
			require self::$ROOT_DOC_DIR . "search.sight.item.php";
		}

		/**
		 * @param string $text
		 * @return string
		 */
		private function highlight($text) {
			$text = htmlspecialchars($text);

			$src = mb_strtolower($text);
			$res = $text;
			$qLength = mb_strlen($this->queryLower);
			$last = 0;

			while (($pos = mb_strrpos($src, $this->queryLower, $last)) !== false) {
				$res = mb_substr($res, 0, $pos) . "<ins>" . mb_substr($res, $pos, $qLength) . "</ins>" . mb_substr($res, $pos + $qLength);
				$last = $pos + 1;
			}

			return $res;
		}

		private $orderKeys = [
			-2 => "по дате обновления (сначала старые)",
			-1 => "по дате добавления (сначала старые)",
			0 => "по умолчанию",
			1 => "по дате добавления (сначала новые)",
			2 => "по дате обновления (сначала обновленные)",
			3 => "по рейтингу"
		];

		/**
		 * @return array[]
		 */
		private function getOrderVariants() {
			$d = [
				["value" => 0],
				["value" => 1],
				["value" => -1],
				["value" => 2],
				["value" => -2],
				["value" => 3]
			];
			foreach ($d as &$item) {
				$item["label"] = $this->orderKeys[$item["value"]];
				$item["selected"] = $item["value"] == $this->order;
			}
			return $d;
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
		 * @param mixed $data
		 * @return boolean
		 */
		public function hasRibbon($data) {
			return $this->city !== null;
		}

		/**
		 * @param $data
		 * @return string|null
		 */
		public function getRibbonImage($data) {
			return null;
		}

		/**
		 * @param mixed $data
		 * @return array|array[]|string|null
		 */
		public function getRibbonContent($data) {
			if ($this->city) {
				/** @var City $city */
				$city = $this->city;
				return [
					$city->getName(),
					$city->getDescription()
				];
			}
			return null;
		}

		/**
		 * @param $data
		 * @return string|boolean
		 */
		public function getRibbonIncludeBlock($data) {
			if ($this->city) {
				return self::$ROOT_DOC_DIR . "search.city.ribbon.php";
			}
			return false;
		}
	}