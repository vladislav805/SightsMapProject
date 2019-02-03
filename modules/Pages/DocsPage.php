<?

	namespace Pages;

	class DocsPage extends BasePage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "API";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");


			$file = self::$ROOT_DOC_DIR . "../../assets/methods.json";

			$data = json_decode(file_get_contents($file));

			if ($action) {

				$method = null;
				foreach ($data as $item) {
					if ($item->name === $action) {
						$method = $item;
						break;
					}
				}

				return [true, $method];
			} else {
				return [false, $data];
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

			$str = preg_replace_callback("/(@|#)\(([^)]+)\)/imu", function($a) {

				list(, $type, $data) = $a;

				$link = $data;

				switch ($type) {
					case "@": $link = sprintf("<a href='/docs/%1\$s'>%1\$s</a>", $data); break;
					case "#": $link = sprintf("<a href='/docs/object_%1\$s'>%1\$s</a>", $data); break;
				}

				return $link;
			}, $str);

			return $str;
		}
	}