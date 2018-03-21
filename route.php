<?

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$act = get("r");
	$id = (int) get("id");

	switch ($act) {
		case "place":
			echo "place";
			exit;

		case "category":
			echo "category";
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