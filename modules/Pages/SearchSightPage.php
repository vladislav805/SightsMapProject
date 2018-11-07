<?

	namespace Pages;

	use Model\ListCount;
	use Model\Params;

	class SearchSightPage extends BasePage {

		protected $query;
		protected $queryLower;

		protected $page;

		protected $count;

		protected function prepare($action) {
			$this->query = get("query");
			$this->page = (int) get("page");
			$this->count = 50;

			$this->queryLower = mb_strtolower($this->query);

			$this->addScript("/pages/js/api.js");

			$result = null;

			if (!empty($this->query)) {
				$params = new Params;
				$params->set("query", $this->query)
					->set("offset", $this->count * $this->page)
					->set("count", $this->count);

				/** @var ListCount $result */
				$result = $this->mController->perform(new \Method\Point\Search($params));
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
	}