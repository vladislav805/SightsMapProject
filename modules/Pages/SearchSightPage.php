<?

	namespace Pages;

	use Model\City;
	use Model\ListCount;
	use Model\Mark;
	use Model\Params;

	class SearchSightPage extends BasePage {

		/** @var string */
		protected $query;

		/** @var int[] */
		protected $markId;

		/** @var int */
		protected $cityId;

		/** @var int */
		protected $page;

		/** @var string */
		protected $queryLower;

		/** @var City|null */
		protected $city;

		/** @var Mark|null */
		protected $mark;

		protected $count;
		protected $order = 0;

		protected $found = false;

		protected function prepare($action) {
			$this->query = get("query");
			$this->markId = (int) get("markId");
			$this->cityId = (int) get("cityId");
			$this->page = (int) get("page");
			$this->order = (int) get("order");
			$this->count = 50;

			if ($this->markId) {
				$mid = explode(",", $this->markId);
				$mid = array_map("intval", $mid);
				$mid = array_unique($mid);
				$mid = array_values(array_filter($mid));
				$this->markId = $mid;
			} else {
				$this->markId = null;
			}

			$this->queryLower = mb_strtolower($this->query);

			$this->addScript("/pages/js/api.js");

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

			if ($this->markId && sizeOf($this->markId)) {
				$params->set("markIds", $this->markId);
				var_dump($this);
				$this->mark = $this->mController->perform(new \Method\Mark\GetById(["markId" => $this->markId[0]]));
			}

			if ($this->cityId) {
				$params->set("cityId", $this->cityId);
				$city = $this->mController->perform(new \Method\City\GetById(["cityIds" => [$this->cityId]]));
				if (sizeOf($city)) {
					$this->city = $city[0];
				}
			}

			$result = null;

			try {
				/** @var ListCount $result */
				/** @noinspection PhpUnhandledExceptionInspection */
				$result = $this->mController->perform(new \Method\Sight\Search($params));

				$this->found = $result->getCount() > 0;
			} catch (\Throwable $e) {
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

		public function getContent($data) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($result) = $data;
			require_once self::$ROOT_DOC_DIR . "search.sight.content.php";
		}

		private function item($item) {
			require self::$ROOT_DOC_DIR . "search.sight.item.php";
		}

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

	}