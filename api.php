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

	$methods = [
		"users.getAuthKey" => "\\Method\\Authorize\\Authorize", // <- string login, string password
		"users.logout" => "\\Method\\Authorize\\Logout", // <-
		"users.getSingle" => "\\Method\\User\\GetById", // <- string|int userId
		"users.get" => "\\Method\\User\\GetByIds", // <- string[]|int[] userIds, boolean extended, string[] extra
		"users.getAchievements" => "\\Method\\User\\GetUserAchievements", // <- int userId
		"users.getCityExperts" => "\\Method\\User\\GetCityExperts", // <- int cityId

		"account.create" => "\\Method\\User\\Registration", // <- string firstName, string lastName, string login, string password, int sex, int cityId
		"account.restore" => null, // <- string hash
		"account.editInfo" => "\\Method\\User\\EditInfo", // <- string firstName, string lastName, int sex, int cityId
		"account.changePassword" => "\\Method\\User\\ChangePassword", // <- string oldPassword, string newPassword
		"account.setProfilePhoto" => "\\Method\\Account\\SetProfilePhoto", // <- int photoId
		"account.removeProfilePhoto" => "\\Method\\Account\\RemoveProfilePhoto", // <-
		"account.setStatus" => "\\Method\\User\\SetOnline", // <- int status

		"sights.get" => "\\Method\\Sight\\Get", // <- double lat1, double lng1, double lat2, double lng2, int[] markId?, boolean onlyVerified
		"sights.getById" => "\\Method\\Sight\\GetById", // <- int sightId
		"sights.add" => "\\Method\\Sight\\Add", // <- string title, string description, double lat, double lng
		"sights.edit" => "\\Method\\Sight\\Edit", // <- int sightId, string title, string description
		"sights.move" => "\\Method\\Sight\\Move", // <- int sightId, double lat, double lng
		"sights.remove" => "\\Method\\Sight\\Remove", // <- int sightId
		"sights.setMarks" => "\\Method\\Sight\\SetMarks", // <- int sightId, int[] markIds
		"sights.setPhotos" => "\\Method\\Sight\\SetPhotos", // <- int sightId, int[] photoIds
		"sights.setVisitState" => "\\Method\\Sight\\SetVisitState", // <- int sightId, int state
		"sights.getVisited" => "\\Method\\Sight\\GetVisited", // <-
		"sights.report" => null, // <- int sightId
		"sights.setVerify" => "\\Method\\Sight\\SetVerify", // <- int sightId, boolean state
		"sights.setArchived" => "\\Method\\Sight\\SetArchived", // <- int sightId, boolean state
		"sights.setParent" => "\\Method\\Sight\\SetParent", // <- int sightId, int parentId
		"sights.getNearby" => "\\Method\\Sight\\GetNearby", // <- double lat, double lng, int distance
		"sights.getVisitCount" => "\\Method\\Sight\\GetVisitCount", // <- int sightId
		"sights.getRandomSightId" => "\\Method\\Sight\\GetRandomSightId", // <-
		"sights.search" => "\\Method\\Sight\\Search", // <- string query, int offset, int count, int cityId, int[] markIds, int visitState, int order, boolean isVerified, boolean isArchived
		"sights.getCounts" => "\\Method\\Sight\\GetCounts", // <-

		"photos.get" => "\\Method\\Photo\\Get", // <- int sightId
		"photos.getById" => "\\Method\\Photo\\GetById", // <- int[] photoIds
		/* @Deprecated */ "photos.upload" => "\\Method\\Photo\\Upload", // <- int type, File file
		"photos.getUploadUri" => "\\Method\\Photo\\GetUploadUri", // <- string type
		"photos.fetchPhoto" => "\\Method\\Photo\\FetchPhoto", // <- string hash
		"photos.save" => "\\Method\\Photo\\Save", // <- string hash
		"photos.remove" => "\\Method\\Photo\\Remove", // <- int photoId
		"photos.getUnsorted" => "\\Method\\Photo\\GetUnsorted", // <- int count, int offset

		// tags.* ?
		"marks.get" => "\\Method\\Mark\\Get", // <-
		"marks.getById" => "\\Method\\Mark\\GetById", // <-
		"marks.add" => "\\Method\\Mark\\Add", // <- string title, int color
		"marks.edit" => "\\Method\\Mark\\Edit", // <- int markId string title, int color
		"marks.remove" => "\\Method\\Mark\\Remove", // <- int markId

		"comments.get" => "\\Method\\Comment\\Get", // <- int sightId
		"comments.add" => "\\Method\\Comment\\Add", // <- int sightId, string text
		"comments.remove" => "\\Method\\Comment\\Remove", // <- int commentId
		"comments.report" => null, // <- int commentId,

		"events.getCount" => "\\Method\\Event\\GetCount", // <-
		"events.get" => "\\Method\\Event\\Get", // <-
		"events.readAll" => "\\Method\\Event\\ReadAll", // <-

		"rating.get" => "\\Method\\Rating\\Get", // <- int sightId
		"rating.set" => "\\Method\\Rating\\Set", // <- int sightId, int rating

		"cities.get" => "\\Method\\City\\Get", // <-
		"cities.getById" => "\\Method\\City\\GetById", // <- int[] cityIds
		"cities.add" => "\\Method\\City\\Add", // <- string name, int parentId, double lat, double lng

		"interests.getInterestInTagsByVisitOfUser" => "\\Method\\Interesting\\GetInterestInTagsByVisitOfUser", // <-
		"interests.getInterestInTagsByRatingOfUser" => "\\Method\\Interesting\\GetInterestInTagsByRatingOfUser", // <-

		"collections.get" => null, // <- int count, int offset, int cityId
		"collections.search" => null, // <- int count, int offset, int cityId, string title
		"collections.create" => null, // <- string title, string text, int[] sightIds
		"collections.edit" => null, // <- int collectionId, string title, string text, int[] sightIds
		"collections.remove" => null, // <- int collectionId

		"router.generate" => null, // <- double lat, double lng, int cityId, int[] markIds, int timeLimit, int lengthLimit
		"neuralNetwork.test" => "\\Method\\NeuralNetwork\\Test2", // <-

		"admin.getUserJobs" => "\\Method\\Admin\\GetUserJobs", // <- int count, int offset
		"admin.setUserJob" => "\\Method\\Admin\\SetUserJob", // <- int userId, string status
		"admin.getBanned" => "\\Method\\Admin\\GetBanned", // <- int count, int offset
		"admin.setBan" => "\\Method\\Admin\\SetBan", // <- int userId, boolean state, string reason

		"execute.compile" => "\\Method\\Execute\\Compile", // <- string code
	];

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
