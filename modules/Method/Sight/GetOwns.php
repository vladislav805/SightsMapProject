<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\ListCount;
	use Model\Sight;
	use Model\User;
	use PDO;

	/**
	 * Получение мест конкретного пользователя
	 * @package Method\Sight
	 */
	class GetOwns extends APIPublicMethod {

		const MAX_LIMIT = 500;

		/** @var int */
		protected $ownerId;

		/** @var int */
		protected $count = 500;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return ListCount
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->ownerId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "ownerId is not specified");
			}

			$this->count = min($this->count, self::MAX_LIMIT);
			$this->offset = min(0, $this->offset);

			$list = $this->getSights($main);

			return $list;
		}

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function getSights($main) {

			$count = "SELECT COUNT(DISTINCT `sight`.`sightId`) AS `count` FROM `sight` WHERE `sight`.`ownerId` = :oid";

			$stmt = $main->makeRequest($count);
			$stmt->execute([":oid" => $this->ownerId]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$countResults = (int) $row["count"];


			$code = <<<SQL
SELECT
	`sight`.*,
    IFNULL(`sightVisit`.`state`, 0) AS `visitState`,
    GROUP_CONCAT(DISTINCT `sightMark`.`markId`) AS `markIds`,
	`city`.`name`,
	`user`.`userId`,
	`user`.`login`,
	`user`.`firstName`,
	`user`.`lastName`,
	`user`.`sex`,
	`user`.`lastSeen`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`type`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	`photo`.`latitude`,
	`photo`.`longitude`,
    `photo`.`prevailColors`,
	getRatedSightByUser(:uid, `sight`.`sightId`) AS `rated`
FROM
	`sight` 
    	LEFT JOIN `city` ON `city`.`cityId` = `sight`.`cityId`
        LEFT JOIN `user` ON `user`.`userId` = `sight`.`ownerId`
        LEFT JOIN `sightVisit` ON `sightVisit`.`sightId` = `sight`.`sightId` AND `sightVisit`.`userId` = :uid
        LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId`
        LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `sightMark` ON  `sightMark`.`sightId` = `sight`.`sightId`
WHERE
	`sight`.`ownerId` = :oid
GROUP BY
	`sight`.`sightId`
ORDER BY
	`sight`.`sightId` DESC
SQL;

			$sql = $code . " LIMIT " . $this->offset . "," . $this->count;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":oid" => $this->ownerId,
				":uid" => $main->isAuthorized() ? $main->getUser()->getId() : 0
			]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$sights = [];
			$users = [];

			foreach ($items as $item) {
				$sights[] = new Sight($item);
				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}
			}

			return (new ListCount($countResults, $sights))->putCustomData("users", array_values($users));
		}

	}