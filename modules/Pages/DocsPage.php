<?

	namespace Pages;

	class DocsPage extends BasePage implements WithBackLinkPage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "API";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/docs-page.js");

			if (mb_strpos($action, "/") !== false) {
				list($sec, $name) = explode("/", $action);
			} else {
				$sec = $action;
				$name = null;
			}

			$data = json_decode(file_get_contents(self::$ROOT_DOC_DIR . "../../assets/api.json"));

			switch ($sec) {
				case "method":
					$method = null;
					foreach ($data->methods as $item) {
						if ($item->name === $name) {
							$method = $item;
							break;
						}
					}

					return ["method", $method];
					break;

				case "object":
					$obj = null;
					foreach ($data->objects as $item) {
						if ($item->name === $name) {
							$obj = $item;
							break;
						}
					}

					return ["object", $obj];
					break;

				default:
					return [null, $data];
			}
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "Docs API";
		}

		public function getContent($data) {
			require_once self::$ROOT_DOC_DIR . "docs.content.php";
		}

		private function parseText($str) {

			$str = nl2br(htmlSpecialChars($str));

			$str = $this->parseFormat($str);

			return $str;
		}

		private function parseFormat($text) {
			return preg_replace_callback("/(@|#)\(([^)]+)\)/imu", function($a) {

				list(, $type, $data) = $a;

				$link = $data;

				switch ($type) {
					case "@": $link = sprintf("<a href='/docs/method/%1\$s'>%1\$s</a>", $data); break;
					case "#": $link = sprintf("<a href='/docs/object/%1\$s'>%1\$s</a>", $data); break;
				}

				return $link;
			}, $text);
		}

		public function getBackURL($data) {
			list($page) = $data;
			return $page !== null ? "/docs" : false;
		}
	}