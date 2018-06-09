<?
	session_start();

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$act = get("r");
	$id = (int) get("id");
	$token = isset($_COOKIE[KEY_TOKEN]) ? $_COOKIE[KEY_TOKEN] : null;

	try {

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);
		$mainController = new MainController($pdo);
		$mainController->setAuthKey($token);


		switch ($act) {
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

			case "":
			case "index":
				require_once "index.php";
				exit;

			case "login":
				require_once "pager/login.php";
				exit;

			default:
				echo "404";
				exit;
		}

	} catch (Exception $e) {
		header("Content-type: text/plain; charset=utf-8");
		print "Error while handling your request.";
		var_dump($e);
	}