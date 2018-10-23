<?

	session_start();

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$r = get("r");
	$id = (int) get("id");
	$token = isset($_COOKIE[KEY_TOKEN]) ? $_COOKIE[KEY_TOKEN] : null;

	try {

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);
		$mainController = new MainController($pdo);
		$mainController->setAuthKey($token);

		$page = null;

		switch ($r) {
			case "place":
				require_once "pager/place.php";
				exit;

			case "manage":
				require_once "pager/manage.php";
				exit;

			case "user":
				require_once "pager/user.php";
				exit;

			case "mark":
				if ($id) {
					require_once "pager/mark.php";
				} else {
					require_once "pager/marks.php";
				}
				exit;

			case "map":
				require_once "pager/map.php";
				exit;

			case "login":
				$page = "Pages\\LoginPage";
				break;

			case "index":
				$page = "Pages\\IndexPage";
				exit;

			case "route2":

				break;

			default:
				echo "404";
				exit;
		}

		if ($page) {
			/** @var \Pages\BasePage $page */
			$page = new $page($mainController, __DIR__ . "/pages");
			ob_start(function($buffer) {
				return preg_replace("/[\t\n]+/", "", $buffer);
			});
			$page->render(get("action"));
			ob_end_flush();
		}

	} catch (Exception $e) {
		header("Content-type: text/plain; charset=utf-8");
		print "Error while handling your request.";
		var_dump($e);
	}