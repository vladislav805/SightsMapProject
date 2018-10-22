<?
	/** @noinspection PhpIncludeInspection */

	namespace Pages;

	use JsonSerializable;
	use Model\Controller;
	use tools\OpenGraph;

	abstract class BasePage implements JsonSerializable {

		private static $ROOT_DOC_DIR;

		/** @var \Model\Controller */
		private $mController;

		/** @var OpenGraph */
		protected $mOpenGraphInfo;

		private $mScripts = [
			"/js-pager/utils.js"
		];

		private $mStyles = [
			"/css/pages.css",
			"/css/ui.css"
		];

		private $mJavaScriptInit;

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

		protected function prepare() {}

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
		 * @return string
		 */
		public abstract function getBrowserTitle();

		/**
		 * @return string
		 */
		public abstract function getPageTitle();

		public abstract function getContent($data);

		public final function render() {
			$result = $this->prepare();

			require_once $this->getTemplateUriTop();
			require_once $this->getTemplateUriHeader();

			print $this->getContent($result);

			require_once $this->getTemplateUriFooter();
			require_once $this->getTemplateUriBottom();
		}

		public final function jsonSerialize() {
			$res = [
				"page" => [
					"title" => $this->getPageTitle(),
					"content" => $this->getContent($this->prepare())
				],
				"internal" => [
					"title" => $this->getBrowserTitle(),
					"scripts" => $this->mScripts,
					"styles" => $this->mStyles,
					"init" => $this->mJavaScriptInit,
					"ts" => time()
				],
			];

			return $res;
		}
	}