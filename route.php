<?

	session_start();

	require_once "autoload.php";
	require_once "methods.php";
	require_once "config.php";
	require_once "functions.php";

	$r = get("r");
	$id = get("id");
	$token = $_COOKIE[KEY_TOKEN] ?? null;

	try {

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);
		$mainController = new MainController($pdo);
		$mainController->setAuthKey($token);

		if ($token) {
			$redis = $mainController->getRedis();

			$key = "uo" . $token;
			$exists = $redis->exists($key);
			if (!$exists || $exists && time() - $redis->get($key) > 5 * MINUTE) {
				try {
					$mainController->perform(new \Method\Account\SetOnline(["status" => true]));
				} /** @noinspection PhpRedundantCatchClauseInspection */ catch (\Method\APIException $ignore) {
					// if token is invalid
				}
			}
		}

		$page = null;

		switch ($r) {
			case "sight":
				if ($action = get("action")) {
					$keywords = [
						"edit" => "Pages\\ManageMapPage",
						"report" => null
					];

					if (isSet($keywords[$action])) {
						$page = $keywords[$action];
					}
				} else {
					$keywords = [
						"random" => "Pages\\RandomSightPage",
						"search" => "Pages\\SearchSightPage",
						"add" => "Pages\\ManageMapPage"
					];

					if (isSet($keywords[$id])) {
						$page = $keywords[$id];
					} else {
						$page = "Pages\\SightPage";
					}
				}
				break;

			case "user":
				$page = "Pages\\UserPage";
				break;

			case "userarea":
				$keywords = [
					"create" => "Pages\\RegisterUserPage",
					"edit" => "Pages\\RegisterUserPage",
					"activation" => "Pages\\ActivationUserPage",
					"vk" => "Pages\\VKAuthUserPage",
					"telegram" => "Pages\\TelegramAuthPage"
				];

				if (isSet($keywords[$id])) {
					$page = $keywords[$id];
				}
				break;

			case "feed":
				$page = "Pages\\FeedPage";
				break;

			case "marks":
				$page = "Pages\\MarkListPage";
				break;

			case "map":
				$page = "Pages\\MapPage";
				break;

			case "login":
				$page = "Pages\\LoginPage";
				break;

			case "index":
				$page = "Pages\\IndexPage";
				break;

			case "docs":
				$page = "Pages\\DocsPage";
				break;

			case "admin":
				$page = "Pages\\AdminPanel";
				break;

			case "neural":
				$page = "Pages\\NeuralPage";
				break;

			default:
				require_once "pages/404.html";
				exit;
		}

		if ($page) {
			if (!class_exists($page)) {
				print "Unknown page class " . $page;
				exit;
			}

			/** @var \Pages\BasePage $page */
			$page = new $page($mainController, __DIR__ . "/pages");

			if (!IS_AJAX && !isset($_REQUEST["_ajax"])) {
				ob_start(function($buffer) {
					return DEBUG ? $buffer : preg_replace("/[\t\n]+/", "", $buffer);
				});
				$page->render(get("action"));
				ob_end_flush();
			} else {
				header("Content-type: application/json; charset=utf-8");
				print json_encode($page, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
			}
		}

	} catch (Throwable $e) {
		header("Content-type: text/plain; charset=utf-8");
		print "Error while handling your request.";
		var_dump($e);
	}