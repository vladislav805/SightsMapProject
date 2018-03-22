<?

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$act = get("r");
	$id = (int) get("id");
	$token = get("token");

	try {

		$mainController = new MainController;
		$mainController->setAuthKey($token);


		switch ($act) {
			case "place":
				require_once "pager/place.php";
				exit;

			case "user":
				require_once "pager/user.php";
				break;

			case "mark":
				if ($id) {
					require_once "pager/mark.php";
				} else {
					require_once "pager/marks.php";
				}
				exit;

			case "map":
				echo "map";
				exit;

			case "":
			case "index":
				require_once "index.php";
				exit;

			default:
				echo "404";
				exit;
		}

	} catch (Exception $e) {
		echo "Error while handling your request.";
		echo "<pre>";
		var_dump($e);
		echo "</pre>";
	}