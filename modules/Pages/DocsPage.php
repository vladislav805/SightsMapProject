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

				case "page":
					$obj = null;
					foreach ($data->pages as $item) {
						if ($item->id === $name) {
							$obj = $item;
							break;
						}
					}

					return ["page", $obj];

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
			return preg_replace_callback("/(@|#|!)\(([^)]+)\)/imu", function($a) {

				list(, $type, $data) = $a;

				$link = $data;

				switch ($type) {
					case "@": $link = sprintf("<a href='/docs/method/%1\$s'>%1\$s</a>", $data); break;
					case "#": $link = sprintf("<a href='/docs/object/%1\$s'>%1\$s</a>", $data); break;
					case "!": $link = sprintf("<a href='/docs/page/%1\$s'>%1\$s</a>", $data); break;
				}

				return $link;
			}, $text);
		}

		public function getBackURL($data) {
			list($page) = $data;
			return $page !== null ? "/docs" : false;
		}

		private function makePageContent($content) {
			foreach ($content as $item) {
				switch ($item->type) {
					case "text":
						printf("<p>%s</p>", $this->parseFormat($item->content));
						break;

					case "table":
						print "<table>";

						$colKey = [];

						if (isset($item->header)) {
							print "<thead><tr>";
							foreach ($item->header as $col) {
								$colKey[] = $col->key;
								printf("<th>%s</th>", $col->title);
							}
							print "</tr></thead>";
						}

						foreach ($item->content as $row) {
							print "<tr>";
							foreach ($colKey as $col) {
								printf("<td>%s</td>", $this->parseFormat($row->{$col}));
							}
							print "</tr>";
						}

						print "</table>";
						break;
				}
			}
		}

	}