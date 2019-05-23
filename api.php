<?

	use Method\APIException;
	use Method\APIMethod;
	use Method\ErrorCode;
	use Model\INotReturnablePublicAPI;

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

	$authKey = get("authKey", null);

	$objMethod = null;

	require_once "methods.php";

	try {

		// Connection to redis
		$redis = getRedis(REDIS_HOST, REDIS_PORT, REDIS_DB, REDIS_PASSWORD, REDIS_TIMEOUT);

		// User-GUID param
		$guid = get("guid", null);

		// Unique string for user
		$uniq = $authKey !== null ? substr($authKey, 0, 16) : $_SERVER["REMOTE_ADDR"];

		// Key for idempotence request
		$rd_key = "api_rd" . $uniq;

		// If guid is specified
		if ($guid !== null) {

			// Add GUID to key
			$rd_key .=  "_" . strtolower($guid);

			// Fetch result
			$res = $redis->get($rd_key);

			// If found, simply send it, stop executing
			if ($res) {
				header("Content-type: application/json; charset=utf-8");
				header("X-Response-Type: cached; token=" . $rd_key);
				print $res;
				exit;
			}

			// If not found - fresh request
		}

		if (API_VERSION < API_VERSION_MIN || API_VERSION > API_VERSION_MAX) {
			throw new APIException(ErrorCode::UNSUPPORTED_API_VERSION, null, sprintf("Using unsupported API version (%d). Supported versions: %d...%d", API_VERSION, API_VERSION_MIN, API_VERSION_MAX));
		}

		$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_HOST, DB_NAME), DB_USER, DB_PASS);

		$mainController = new MainController($pdo);
		$mainController->setAuthKey($authKey);
		$mainController->setRedis($redis);

		if (isset($methods[$method])) {

			/** @var APIMethod $methodObject */
			$methodObject = new $methods[$method]($_REQUEST);

			if ($methodObject instanceof INotReturnablePublicAPI) {
				throw new APIException(ErrorCode::UNKNOWN_METHOD);
			}

			done($mainController->perform($methodObject), "result", $redis,  $rd_key);
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
		done($e, "error", $redis, $rd_key);
	}
