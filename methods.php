<?
	$methods = [
		"users.getAuthKey" => "\\Method\\Authorize\\Authorize", // <- string login, string password
		"users.logout" => "\\Method\\Authorize\\Logout", // <-
		"users.getSingle" => "\\Method\\User\\GetById", // <- string|int userId
		"users.get" => "\\Method\\User\\GetByIds", // <- string[]|int[] userIds, boolean extended, string[] extra
		"users.getAchievements" => "\\Method\\User\\GetUserAchievements", // <- int userId
		"users.getCityExperts" => "\\Method\\User\\GetCityExperts", // <- int cityId

		"account.create" => "\\Method\\Account\\Registration", // <- string firstName, string lastName, string login, string password, string sex, int cityId
		"account.restore" => null, // <- string hash
		"account.editInfo" => "\\Method\\Account\\EditInfo", // <- string firstName, string lastName, string sex, int cityId
		"account.changePassword" => "\\Method\\Account\\ChangePassword", // <- string oldPassword, string newPassword
		"account.setProfilePhoto" => "\\Method\\Account\\SetProfilePhoto", // <- int photoId
		"account.removeProfilePhoto" => "\\Method\\Account\\RemoveProfilePhoto", // <-
		"account.setStatus" => "\\Method\\Account\\SetOnline", // <- int status

		"sights.get" => "\\Method\\Sight\\Get", // <- double lat1, double lng1, double lat2, double lng2, int[] markId?, boolean onlyVerified
		"sights.getById" => "\\Method\\Sight\\GetById", // <- int sightId
		"sights.add" => "\\Method\\Sight\\Add", // <- string title, string description, double lat, double lng
		"sights.edit" => "\\Method\\Sight\\Edit", // <- int sightId, string title, string description
		"sights.move" => "\\Method\\Sight\\Move", // <- int sightId, double lat, double lng
		"sights.remove" => "\\Method\\Sight\\Remove", // <- int sightId
		"sights.setMarks" => "\\Method\\Sight\\SetMarks", // <- int sightId, int[] markIds
		"sights.setPhotos" => "\\Method\\Sight\\SetPhotos", // <- int sightId, int[] photoIds
		"sights.suggestPhoto" => "\\Method\\Sight\\SuggestPhoto", // <- int sightId, int photoId
		"sights.approvePhoto" => "\\Method\\Sight\\ApprovePhoto", // <- int sightId, int photoId
		"sights.declinePhoto" => "\\Method\\Sight\\DeclinePhoto", // <- int sightId, int photoId
		"sights.setVisitState" => "\\Method\\Sight\\SetVisitState", // <- int sightId, int state
		"sights.getVisited" => "\\Method\\Sight\\GetVisited", // <
		"sights.setVerify" => "\\Method\\Sight\\SetVerify", // <- int sightId, boolean state
		"sights.setArchived" => "\\Method\\Sight\\SetArchived", // <- int sightId, boolean state
		"sights.setParent" => "\\Method\\Sight\\SetParent", // <- int sightId, int parentId
		"sights.getNearby" => "\\Method\\Sight\\GetNearby", // <- double lat, double lng, int distance
		"sights.getVisitCount" => "\\Method\\Sight\\GetVisitCount", // <- int sightId
		"sights.getRandomSightId" => "\\Method\\Sight\\GetRandomSightId", // <-
		"sights.search" => "\\Method\\Sight\\Search", // <- string query, int offset, int count, int cityId, int[] markIds, int visitState, int order, boolean isVerified, boolean isArchived
		"sights.getCounts" => "\\Method\\Sight\\GetCounts", // <-
		"sights.getReportReasons" => "\\Method\\Sight\\GetReportReasons", // <- int sightId
		"sights.report" => "\\Method\\Sight\\Report", // <- int sightId, int reasonId, string comment
		"sights.getOwns" => "\\Method\\Sight\\GetOwns", // <- int ownerId, int count, int offset

		"photos.get" => "\\Method\\Photo\\Get", // <- int sightId
		"photos.getById" => "\\Method\\Photo\\GetById", // <- int[] photoIds
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
		"cities.add" => "\\Method\\City\\Add", // <- string name, int parentId, double lat, double lng, int radius, string description
		"cities.edit" => "\\Method\\City\\Edit", // <- int cityId, string name, int parentId, double lat, double lng, int radius, string description
		"cities.remove" => "\\Method\\City\\Remove", // <- int cityId

		"interests.getInterestInTagsByVisitOfUser" => "\\Method\\Interesting\\GetInterestInTagsByVisitOfUser", // <-
		"interests.getInterestInTagsByRatingOfUser" => "\\Method\\Interesting\\GetInterestInTagsByRatingOfUser", // <-

		"collections.get" => "\\Method\\Collection\\Get", // <- int count, int offset, int cityId
		"collections.getCollection" => null, // <- int collectionId
		"collections.search" => null, // <- int count, int offset, int cityId, string title
		"collections.add" => "\\Method\\Collection\\Add", // <- string title, string description, int cityId, string type
		"collections.edit" => null, // <- int collectionId, string title, string text, int[] sightIds
		"collections.setSightsList" => null, // <- int collectionId, int[] sightIds
		"collections.remove" => "\\Method\\Collection\\Remove", // <- int collectionId

		"router.generate" => null, // <- double lat, double lng, int cityId, int[] markIds, int timeLimit, int lengthLimit
		"neuralNetwork.getInterestedSights" => "\\Method\\NeuralNetwork\\GetInterestedSights", // <- boolean forceRebuildNetwork, int count, int offset
		"neuralNetwork.getParametersForRouting" => "\\Method\\NeuralNetwork\\GetParametersForRouting", // <-
		"neuralNetwork.test" => "\\Method\\NeuralNetwork\\Test2", // <-

		"admin.getUserJobs" => "\\Method\\Admin\\GetUserJobs", // <- int count, int offset
		"admin.setUserJob" => "\\Method\\Admin\\SetUserJob", // <- int userId, string status
		"admin.getBanned" => "\\Method\\Admin\\GetBanned", // <- int count, int offset
		"admin.setBan" => "\\Method\\Admin\\SetBan", // <- int userId, boolean state, string reason
		"admin.getReportedSights" => "\\Method\\Admin\\GetReportedSights", // <- int count, int offset

		"execute.compile" => "\\Method\\Execute\\Compile", // <- string code
	];