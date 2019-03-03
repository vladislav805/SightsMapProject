<?
	/** @noinspection PhpUnusedLocalVariableInspection */

	namespace Pages;

	use Method\Admin\GetBanned;
	use Method\Admin\GetUserJobs;

	class AdminPanel extends BasePage implements WithBackLinkPage {

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Admin | Sights Map";
		}

		protected function prepare($action) {
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/admin-page.js");
			$this->addStylesheet("/css/admin.css");

			$page = null;
			$data = [];
			switch ($action) {
				case "moderators":
					$page = "moderator";
					$data = [$this->mController->perform(new GetUserJobs([]))];
					break;

				case "ban":
					$page = "ban";
					$data = [$this->mController->perform(new GetBanned([]))];
					break;

				default:
					$page = "initial";
			}

			return [$page, $data];
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return "Admin panel";
		}

		public function getJavaScriptInit($data) {
			list($page) = $data;

			switch ($page) {
				case "ban": return "onReady(e => Admin.initBanPage());"; break;
				case "moderator": return "onReady(e => Admin.initJobsPage());"; break;
			}
		}

		/**
		 * @param array $d
		 * @return void
		 */
		public function getContent($d) {

			list($page, $data) = $d;

			include self::$ROOT_DOC_DIR . "admin/" . $page . ".content.php";


		}

		public function getBackURL($data) {
			list($page) = $data;
			return $page !== "initial" ? "/admin" : false;
		}
	}