<?

	ini_set("display_errors", "on");
	error_reporting(E_ALL);

	spl_autoload_register(function ($class) {
		/** @noinspection PhpIncludeInspection */
		include_once "modules/" . str_replace("\\", "/", $class) . ".php";
	});

	require_once "config.php";
	require_once "functions.php";

	$method = get("method");

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control");

	$authKey = get("authKey");
	$mainController = new MainController;
	$mainController->setAuthKey($authKey);

	$objMethod = null;

	$methods = [
		"users.getAuthKey" => "\\Method\\Authorize\\Authorize", // <- string login, string password
		"users.logout" => "\\Method\\Authorize\\Logout", // <-
		"users.get" => "\\Method\\User\\GetByIds", // <- string[]|int[] userIds

		"registration.restore" => null, // <- string hash

		"account.create" => "\\Method\\User\\Registration", // <- string firstName, string lastName, string login, string password, int sex
		"account.editInfo" => "\\Method\\User\\EditInfo", // <- string firstName, string lastName, int sex, string login
		"account.changePassword" => "\\Method\\User\\ChangePassword", // <- string oldPassword, string newPassword
		"account.setStatus" => "\\Method\\User\\SetStatus", // <- int status

		"points.get" => "\\Method\\Point\\Get", // <- double lat1, double lng1, double lat2, double lng2, int[] markId?, boolean onlyVerified
		"points.add" => "\\Method\\Point\\Add", // <- string title, string description, double lat, double lng
		"points.edit" => "\\Method\\Point\\Edit", // <- int pointId, string title, string description
		"points.move" => "\\Method\\Point\\Move", // <- int pointId, double lat, double lng
		"points.remove" => "\\Method\\Point\\Remove", // <- int pointId
		"points.setMarks" => "\\Method\\Point\\SetMarks", // <- int pointId, int[] markIds
		"points.setPhotos" => "\\Method\\Point\\SetPhotos", // <- int pointId, int[] photoIds
		"points.setVisitState" => "\\Method\\Point\\SetVisitState", // <- int pointId, int state
		"points.getVisited" => "\\Method\\Point\\GetVisited", // <-
//		"points.report" => "\\Method\\Point\\Report", // <- int pointId
		"points.setVerify" => "\\Method\\Point\\SetVerify", // <- int pointId

		"photos.get" => "\\Method\\Photo\\Get", // <- int pointId
		"photos.getById" => "\\Method\\Photo\\GetById", // <- int[] photoIds
		"photos.upload" => "\\Method\\Photo\\Upload", // <- int type, File file
		"photos.remove" => "\\Method\\Photo\\Remove", // <- int photoId

		"marks.get" => "\\Method\\Mark\\Get", // <-
		"marks.add" => "\\Method\\Mark\\Add", // <- string title, int color
		"marks.edit" => "\\Method\\Mark\\Edit", // <- int markId string title, int color
		"marks.remove" => "\\Method\\Mark\\Remove", // <- int markId

		"comments.get" => "\\Method\\Comment\\Get", // <- int pointId
		"comments.add" => "\\Method\\Comment\\Add", // <- int pointId, string text
		"comments.remove" => "\\Method\\Comment\\Remove", // <- int commentId
//		"comments.report" => "\Method\Comment\Report" // <- int commentId,

		"events.get" => "\\Method\\Event\\Get", // <-
		"events.readAll" => "\\Method\\Event\\ReadAll", // <-


	];

	try {

		if (isset($methods[$method])) {
			done($mainController->perform(new $methods[$method]($_REQUEST)));
		} else {
			throw new APIException(ERROR_UNKNOWN_METHOD);
		}
	} catch (Exception $e) {
		done($e, "error");
	}
