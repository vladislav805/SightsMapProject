<?

	use Method\APIException;
	use Method\ErrorCode;

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$method = get("method");

	$version = (int) get("v", 200);

	define("API_VERSION", $version);

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control");

	$authKey = get("authKey");

	$objMethod = null;

	require_once "methods.php";

	try {

		if (API_VERSION < API_VERSION_MIN || API_VERSION > API_VERSION_MAX) {
			throw new APIException(ErrorCode::UNSUPPORTED_API_VERSION, null, sprintf("Using unsupported API version (%d). Supported versions: %d...%d", API_VERSION, API_VERSION_MIN, API_VERSION_MAX));
		}

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);
		$mainController = new MainController($pdo);
		$mainController->setAuthKey($authKey);

		if (isset($methods[$method])) {
			done($mainController->perform(new $methods[$method]($_REQUEST)));
		} else {
			throw new APIException(ErrorCode::UNKNOWN_METHOD, null, "Unknown method passed");
		}
	} catch (Throwable $e) {
		if (!($e instanceof JsonSerializable)) {
			$e = [
				"error" => sprintf("Internal API error: throw unhandled exception %s", get_class($e)),
				"message" => $e->getMessage(),
				"file" => $e->getFile(),
				"line" => $e->getLine(),
				"code" => $e->getCode(),
				"trace" => $e->getTrace()
			];
		}
		done($e, "error");
	}
