<?

	session_start();

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$r = get("r");
	$id = get("id");
	$token = isset($_COOKIE[KEY_TOKEN]) ? $_COOKIE[KEY_TOKEN] : null;

	try {

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);
		$mainController = new MainController($pdo);
		$mainController->setAuthKey($token);

		$page = null;

		switch ($r) {
			case "place":
				$keywords = [
					"random" => "Pages\\RandomSightPage"
				];

				if (isSet($keywords[$id])) {
					$page = $keywords[$id];
				} else {
					$page = "Pages\\SightPage";
				}
				break;

			case "manage":
				/*$keywords = [
					"add" => "Pages\\AddSightPage",
					"search" => "Pages\\SearchSightPage"
				];

				if (isSet($keywords[$id])) {
					$page = $keywords[$id];
				}
				*/
				require_once "pager/manage.php";
				exit;

			case "user":
				$keywords = [
					"registration" => "Pages\\RegisterUserPage",
					"activation" => "Pages\\ActivationUserPage",
					"vk" => "Pages\\VKAuthUserPage"
				];

				if (isSet($keywords[$id])) {
					$page = $keywords[$id];
				} else {
					$page = "Pages\\UserPage";
				}
				break;

			/*
			case "feed":
				$page = "Pages\\FeedPage";
				break;
			*/

			case "mark":
				if ($id) {
					require_once "pager/mark.php";
				} else {
					require_once "pager/marks.php";
				}
				exit;

			case "map":
				$page = "Pages\\MapPage";
				break;

			case "login":
				$page = "Pages\\LoginPage";
				break;

			case "index":
				$page = "Pages\\IndexPage";
				break;

			default:
				echo "404";
				exit;
		}

		if ($page) {
			if (!class_exists($page)) {
				print "Unknown page class " . $page;
				exit;
			}

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