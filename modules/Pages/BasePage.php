<?
	/** @noinspection PhpIncludeInspection */

	namespace Pages;

	use JsonSerializable;
	use Method\APIException;
	use Method\ErrorCode;
	use Model\Controller;
	use tools\OpenGraph;

	abstract class BasePage implements JsonSerializable {

		protected static $ROOT_DOC_DIR;

		/** @var \Model\Controller */
		protected $mController;

		/** @var OpenGraph */
		protected $mOpenGraphInfo;

		private $mScripts = [
			"/lib/sugar.min.js",
			"/pages/js/utils.js",
			"/pages/js/ui/toast.js",
			"/pages/js/ajax.js"
		];

		private $mStyles = [
			"/css/pages.css"
		];

		protected $mClassBody = [];

		public function __construct(Controller $controller, string $dir) {
			$this->mController = $controller;
			self::$ROOT_DOC_DIR = $dir . "/html/";
		}

		protected function hasOpenGraph() {
			return $this->mOpenGraphInfo !== null;
		}

		protected function addClassBody($cls) {
			$this->mClassBody[] = $cls;
		}

		protected function getTemplateUriTop() { return self::$ROOT_DOC_DIR . "default.top.php"; }
		protected function getTemplateUriHeader() { return self::$ROOT_DOC_DIR . "default.head.php"; }
		protected function getTemplateUriFooter() { return self::$ROOT_DOC_DIR . "default.foot.php"; }
		protected function getTemplateUriBottom() { return self::$ROOT_DOC_DIR . "default.bottom.php"; }

		protected function prepare($action) {}

		/**
		 * Stylesheets
		 */

		/**
		 * @param string $uri
		 */
		public function addStylesheet($uri) {
			$this->mStyles[] = $uri;
		}

		public final function pullStyles() {
			return join("", array_map(function($uri) {
				/** @noinspection HtmlUnknownTarget */
				return sprintf("<link rel=\"stylesheet\" href=\"%s\" />", $uri);
			}, $this->mStyles));
		}

		/**
		 * JavaScripts
		 */

		/**
		 * @param string $uri
		 */
		public function addScript($uri) {
			$this->mScripts[] = $uri;
		}

		public final function pullScripts() {
			return join("", array_map(function($uri) {
				/** @noinspection HtmlUnknownTarget */
				return sprintf("<script src=\"%s\"></script>", $uri);
			}, $this->mScripts));
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public abstract function getBrowserTitle($data);

		/**
		 * @param mixed $data
		 * @return string
		 */
		public abstract function getPageTitle($data);

		/**
		 * @param mixed $data
		 * @return mixed
		 */
		public abstract function getContent($data);

		/**
		 * @param string $action
		 */
		public final function render($action) {
			$data = $this->prepare($action);
			try {
				if ($this->mController->getSession()) {
					$this->addClassBody("site--user-authorized");
				} else {
					$this->addClassBody("site--user-passerby");
				}
			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
				if ($e->getCode() === ErrorCode::SESSION_NOT_FOUND) {
					setCookie(KEY_TOKEN, null, 0, "/");
					redirectTo("/login?act=logout&repath=" . urlencode($_SERVER["REQUEST_URI"]));
					exit;
				}
			}

			if ($this instanceof VirtualPage) {
				exit;
			}

			if ($this->getTemplateUriTop()) {
				require_once $this->getTemplateUriTop();
			}

			if ($this->getTemplateUriHeader()) {
				require_once $this->getTemplateUriHeader();
			}

			print $this->getContent($data);

			if ($this->getTemplateUriFooter()) {
				require_once $this->getTemplateUriFooter();
			}

			if ($this->getTemplateUriBottom()) {
				require_once $this->getTemplateUriBottom();
			}
		}


		public function getJavaScriptInit(/** @noinspection PhpUnusedParameterInspection */ $data) {
			return null;
		}


		public final function jsonSerialize() {
			$data = $this->prepare(get("action"));

			$htmlTrimmerTabsSpaces = function($buffer) {
				return DEBUG ? $buffer : preg_replace("/[\t\n]+/", "", $buffer);
			};

			ob_start($htmlTrimmerTabsSpaces);
			$this->getContent($data);
			$content = ob_get_contents();
			ob_clean();

			$ribbon = null;


			if ($this instanceOf RibbonPage) {
				ob_start($htmlTrimmerTabsSpaces);
				$rb = $this->getRibbonContent($data);
				if (is_array($rb)) {
					$ribbon = $rb;
				} else {
					$ribbon = ob_get_contents();
				}
				ob_clean();
			}

			$res = [
				"page" => [
					"title" => $this->getPageTitle($data),
					"content" => $content,
					"bodyClass" => join(" ", $this->mClassBody)
				],
				"internal" => [
					"title" => $this->getBrowserTitle($data),
					"scripts" => $this->mScripts,
					"styles" => $this->mStyles,
					"init" => $this->getJavaScriptInit($data),
					"ts" => time()
				],
			];

			if ($this instanceof RibbonPage) {
				$res["ribbon"] = [
					"image" => $this->getRibbonImage($data),
					"content" => $ribbon
				];
			}

			if ($this instanceof WithBackLinkPage) {
				$res["backLink"] = [
					"url" => $this->getBackURL($data)
				];
			}

			return $res;
		}

		protected function error($id) {
			switch ($id) {
				case 403:
					header("HTTP/1.1 403 Forbidden");
					print "403 forbidden";
					break;

				case 404:
					header("HTTP/1.1 404 Not Found");
					print "404 not found";
					break;
			}
			exit;
		}
	}