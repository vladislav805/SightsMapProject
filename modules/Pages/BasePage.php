<?
	/** @noinspection PhpIncludeInspection */

	namespace Pages;

	use JsonSerializable;
	use Model\Controller;
	use tools\OpenGraph;

	abstract class BasePage implements JsonSerializable {

		protected static $ROOT_DOC_DIR;

		/** @var \Model\Controller */
		protected $mController;

		/** @var OpenGraph */
		protected $mOpenGraphInfo;

		private $mScripts = [
			"/js-pager/utils.js"
		];

		private $mStyles = [
			"/css/pages.css",
			"/css/ui.css"
		];

		protected $mClassBody; // index = page-index

		public function __construct(Controller $controller, string $dir) {
			$this->mController = $controller;
			self::$ROOT_DOC_DIR = $dir . "/html/";
		}

		protected function hasOpenGraph() {
			return $this->mOpenGraphInfo !== null;
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

		/**
		 * @return string
		 */
		public function getJavaScriptInit() {
			return "";
		}


		public final function jsonSerialize() {
			$data = $this->prepare(get("action"));
			$res = [
				"page" => [
					"title" => $this->getPageTitle($data),
					"content" => $this->getContent($data)
				],
				"internal" => [
					"title" => $this->getBrowserTitle($data),
					"scripts" => $this->mScripts,
					"styles" => $this->mStyles,
					"init" => $this->getJavaScriptInit(),
					"ts" => time()
				],
			];

			if ($this instanceOf RibbonPage) {
				$res["ribbon"] = [
					"image" => $this->getRibbonImage($data),
					"content" => $this->getRibbonContent($data)
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