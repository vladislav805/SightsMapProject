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


			$file = self::$ROOT_DOC_DIR . "../../assets/methods.txt";

			$lines = array_map(function($line) {
				$line = trim($line);
				list($method, $_params) = explode(":", $line);
				$params = [];
				if (!empty($_params)) {
					$params = array_map(function($arg) {
						list($type, $name) = explode(" ", trim($arg));
						return [
							"type" => $type,
							"name" => $name
						];
					}, explode(", ", $_params));
				}

				return [
					"name" => $method,
					"params" => $params
				];
			}, file($file));

			$methods = [];
			foreach ($lines as $method) {
				$methods[$method["name"]] = $method;
			}

			if ($action && isset($methods[$action])) {
				return [true, $methods[$action]];
			} else {
				return [false, $methods];
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
	}