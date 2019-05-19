<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\ListCount;
	use Model\Sight;
	use Model\StandaloneCity;
	use Model\User;
	use PDO;

	/**
	 * Получение мест в заданном куске карты по двум точкам по двум координатам
	 * @package Method\Sight
	 */
	class Get extends APIPublicMethod {

		const MAX_LIMIT = 500;

		/** @var double */
		protected $lat1;

		/** @var double */
		protected $lng1;

		/** @var double */
		protected $lat2;

		/** @var double */
		protected $lng2;

		/** @var int */
		protected $markId;

		/** @var boolean */
		protected $onlyVerified;

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
			if (!($this->lat1 && $this->lat2 && $this->lng1 && $this->lng2)) {
				throw new APIException(ErrorCode::NO_PARAM, null, "lat1, lat2, lng1, lng2 not specified");
			}

			$lat1 = min($this->lat1, $this->lat2);
			$lat2 = max($this->lat1, $this->lat2);
			$lng1 = min($this->lng1, $this->lng2);
			$lng2 = max($this->lng1, $this->lng2);

			$this->lat1 = $lat1;
			$this->lat2 = $lat2;
			$this->lng1 = $lng1;
			$this->lng2 = $lng2;

			if (abs($lat2 - $lat1) > .35 || abs($lng2 - $lng1) > .9) {
				return $this->getCities($main, abs($lat2 - $lat1) < 2);
			}

			$this->count = min($this->count, self::MAX_LIMIT);
			$this->offset = min(0, $this->offset);

			$list = $this->getSightsInArea($main);


			if ($main->isAuthorized()) {
				$user = $main->getUser();
			} else {
				$user = null;
			}

			$items = $list->getItems();
			array_walk($items, function(Sight $sight) use ($user) {
				$user && $sight->setAccessByCurrentUser($user);
				return $sight;
			});

			return $list;
		}

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function getSightsInArea($main) {
			if ($this->onlyVerified) {
				$condition[] = "`isVerified` = '1'";
			}

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
	(`sight`.`lat` BETWEEN :lat1 AND :lat2) AND
    (`sight`.`lng` BETWEEN :lng1 AND :lng2) AND
	`sight`.`parentId` IS NULL
GROUP BY
	`sight`.`sightId`
ORDER BY
	`sight`.`sightId` DESC
SQL;

			$sql = $code . " LIMIT " . $this->offset . ",". $this->count;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":lat1" => $this->lat1,
				":lat2" => $this->lat2,
				":lng1" => $this->lng1,
				":lng2" => $this->lng2,
				":uid" => $main->isAuthorized() ? $main->getUser()->getId() : 0
			]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$users = [];
			$sights = [];

			foreach ($items as $item) {
				$sights[] = new Sight($item);
				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}
			}

			$list = new ListCount(sizeOf($items), $sights);
			$list->putCustomData("type", "sights");
			$list->putCustomData("users", array_values($users));
			return $list;
		}

		/**
		 * @param IController $main
		 * @param boolean$needChildren
		 * @return ListCount
		 */
		private function getCities($main, $needChildren = true) {
			$additional = $needChildren ? "" : " AND `city`.`parentId` IS NULL";

			$code = <<<CODE
SELECT
	`city`.*,
	COUNT(`sight`.`sightId`) AS `count`
FROM
	`city` LEFT JOIN `sight` ON `city`.`cityId` = `sight`.`cityId`
WHERE
	(`city`.`lat` BETWEEN :lat1 AND :lat2) AND (`city`.`lng` BETWEEN :lng1 AND :lng2) {$additional}
GROUP BY `sight`.`cityId` 
CODE;

			$stmt = $main->makeRequest($code);
			$stmt->execute([
				":lat1" => $this->lat1,
				":lat2" => $this->lat2,
				":lng1" => $this->lng1,
				":lng2" => $this->lng2
			]);

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			array_walk($items, function(&$city) {
				$city = new StandaloneCity($city);
			});

			$list = new ListCount(sizeOf($items), $items);
			$list->putCustomData("type", "cities");
			$list->putCustomData("filtered", $needChildren ? "all" : "important");
			return $list;
		}
	}