<?

	use Method\APIException;
	use Method\ErrorCode;

	require_once "autoload.php";
	require_once "config.php";
	require_once "functions.php";

	$method = get("method");

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control");

	$authKey = get("authKey");

	$objMethod = null;

	$methods = [
		"users.getAuthKey" => "\\Method\\Authorize\\Authorize", // <- string login, string password
		"users.logout" => "\\Method\\Authorize\\Logout", // <-
		"users.get" => "\\Method\\User\\GetByIds", // <- string[]|int[] userIds
		"users.getAchievements" => "\\Method\\User\\GetUserAchievements", // <- int userId

		"account.create" => "\\Method\\User\\Registration", // <- string firstName, string lastName, string login, string password, int sex
		"account.restore" => null, // <- string hash
		"account.editInfo" => "\\Method\\User\\EditInfo", // <- string firstName, string lastName, int sex, string login
		"account.changePassword" => "\\Method\\User\\ChangePassword", // <- string oldPassword, string newPassword
		"account.setStatus" => "\\Method\\User\\SetOnline", // <- int status

		"points.get" => "\\Method\\Sight\\Get", // <- double lat1, double lng1, double lat2, double lng2, int[] markId?, boolean onlyVerified
		"points.getById" => "\\Method\\Sight\\GetById", // <- int pointId
		"points.add" => "\\Method\\Sight\\Add", // <- string title, string description, double lat, double lng
		"points.edit" => "\\Method\\Sight\\Edit", // <- int pointId, string title, string description
		"points.move" => "\\Method\\Sight\\Move", // <- int pointId, double lat, double lng
		"points.remove" => "\\Method\\Sight\\Remove", // <- int pointId
		"points.setMarks" => "\\Method\\Sight\\SetMarks", // <- int pointId, int[] markIds
		"points.setPhotos" => "\\Method\\Sight\\SetPhotos", // <- int pointId, int[] photoIds
		"points.setVisitState" => "\\Method\\Sight\\SetVisitState", // <- int pointId, int state
		"points.getVisited" => "\\Method\\Sight\\GetVisited", // <-
//		"points.report" => "\\Method\\Point\\Report", // <- int pointId
		"points.setVerify" => "\\Method\\Sight\\SetVerify", // <- int pointId, boolean state
		"points.setArchived" => "\\Method\\Sight\\SetArchived", // <- int pointId, boolean state
		"points.getNearby" => "\\Method\\Sight\\GetNearby", // <- double lat, double lng, float distance
		"points.getVisitCount" => "\\Method\\Sight\\GetVisitCount", // <- int pointId
		"points.getPopular" => "\\Method\\Sight\\GetPopular", // <-
		"points.getRandomPlace" => "\\Method\\Sight\\GetRandomPlace", // <-
		"points.search" => "\\Method\\Sight\\Search", // <- string query, int offset, int count, int cityId, int[] markIds, int visitState, int order, boolean isVerified, boolean isArchived
		"points.getCounts" => "\\Method\\Sight\\GetCounts", // <-

		"photos.get" => "\\Method\\Photo\\Get", // <- int pointId
		"photos.getById" => "\\Method\\Photo\\GetById", // <- int[] photoIds
		/* @Deprecated */ "photos.upload" => "\\Method\\Photo\\Upload", // <- int type, File file
		"photos.getUploadUri" => "\\Method\\Photo\\GetUploadUri", // <- string type
		"photos.fetchPhoto" => "\\Method\\Photo\\FetchPhoto", // <- string hash
		"photos.save" => "\\Method\\Photo\\Save", // <- string hash
		"photos.remove" => "\\Method\\Photo\\Remove", // <- int photoId
		"photos.getUnsorted" => "\\Method\\Photo\\GetUnsorted", // <- int count, int offset

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

		"rating.get" => "\\Method\\Rating\\Get", // <- int pointId
		"rating.set" => "\\Method\\Rating\\Set", // <- int pointId, int rating

		"cities.get" => "\\Method\\City\\Get", // <-
		"cities.add" => null, // <- double lat, double lng, string title, int parentId

		"interests.getInterestInTagsByVisitOfUser" => "\\Method\\Interesting\\GetInterestInTagsByVisitOfUser", // <-
		"interests.getInterestInTagsByRatingOfUser" => "\\Method\\Interesting\\GetInterestInTagsByRatingOfUser", // <-

		"collections.get" => null, // <- int count, int offset, int cityId
		"collections.search" => null, // <- int count, int offset, int cityId, string title
		"collections.create" => null, // <- string title, string text, int[] pointIds
		"collections.edit" => null, // <- int collectionId, string title, string text, int[] pointIds
		"collections.remove" => null, // <- int collectionId

		"router.generate" => null, // <- double lat, double lng, int cityId, int[] markIds, int timeLimit, int lengthLimit

		"moderators.get" => null, // <- int count, int offset
		"moderators.promote" => null, // <- int userId,

		"bannedUsers.get" => null, // <- int count, int offset
		"bannedUsers.set" => null, // <- int userId, boolean state, string reason

		"execute.compile" => "\\Method\\Execute\\Compile", // <- string code

		"__points.getOwns" => "\\Method\\Sight\\GetOwns", // <- int ownerId
	];

	try {

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
			$e = sprintf("Internal API error: throw unhandled exception %s", get_class($e));
		}
		done($e, "error");
	}
