<?php

	namespace Method\User;

	use Constant\VisitState;
	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use PDO;

	/**
	 * расчет "достижений" пользователя
	 * @package Method\User
	 */
	class GetUserAchievements extends APIPublicMethod {

		/** @var  int */
		protected $userId;

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
SELECT 
       (SELECT COUNT(*) FROM `sightVisit` WHERE `userId` = :uid AND `state` = :state) AS `visitedSights`,
       (SELECT COUNT(*) FROM `sight` WHERE `ownerId` = :uid AND `isVerified` = 1) AS `authorOfSights`,
       (SELECT COUNT(*) FROM `sight` WHERE `ownerId` = :uid) AS `authorOfAllSights`,
       (0) AS `authorOfCollections`,
       (SELECT COUNT(*) FROM `photo` WHERE `ownerId` = :uid AND `type` = :photoType) AS `photosOfSights`,
       (SELECT COUNT(*) FROM `comment` WHERE `userId` = :uid) AS `comments`
SQL;

			$stmt = $main->makeRequest($sql);

			if (!$this->userId) {
				if (!$main->isAuthorized()) {
					throw new APIException(ErrorCode::NO_PARAM, null, "userId is required if authKey not passed");
				}
				$this->userId = $main->getSession()->getUserId();
			}

			$stmt->execute([
				":uid" => $this->userId,
				":state" => VisitState::VISITED,
				":photoType" => Photo::TYPE_SIGHT
			]);

			$res = $stmt->fetch(PDO::FETCH_ASSOC);

			foreach ($res as &$item) {
				$item = (int) $item;
			}
			unset($item);

			return $res;
		}
	}