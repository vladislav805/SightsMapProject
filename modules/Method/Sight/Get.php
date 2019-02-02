<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Method\Mark\GetByPoints;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Sight;
	use Model\StandaloneCity;
	use Model\User;
	use PDO;

	/**
	 * Получение мест в заданном куске карты по двум точкам по двум координатам
	 * @package Method\Point
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
				return $this->getCities($main, abs($lat2 - $lat1) < 1.6);
			}

			$this->count = min($this->count, self::MAX_LIMIT);
			$this->offset = min(0, $this->offset);

			$list = $this->getPointsInArea($main);

			$pointIds = array_map(function(Sight $placemark) {
				return $placemark->getId();
			}, $list->getItems());

			$marks = $main->perform(new GetByPoints((new Params)->set("sightIds", $pointIds)));

			if ($main->isAuthorized()) {
				$user = $main->getUser();
				$visited = $main->perform(new GetVisited(new Params));
			} else {
				$visited = null;
				$user = null;
			}

			$items = $list->getItems();
			array_walk($items, function(Sight $placemark) use ($user, $marks, $visited) {
				$user && $placemark->setAccessByCurrentUser($user);
				$id = $placemark->getId();
				if (isset($marks[$id])) {
					$placemark->setMarks($marks[$placemark->getId()]);
				}
				$visited && $placemark->setVisitState(isset($visited[$id]) ? $visited[$id] : 0);
				return $placemark;
			});

			return $list;
		}

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function getPointsInArea($main) {
			if ($this->onlyVerified) {
				$condition[] = "`isVerified` = '1'";
			}

			$code = <<<SQL
SELECT
	DISTINCT `p`.`pointId`,
    `p`.`ownerId`,
	`p`.`lat`,
    `p`.`lng`,
    `p`.`dateCreated`,
    `p`.`dateUpdated`,
    `p`.`isVerified`,
    `p`.`isArchived`,
    `p`.`description`,
    `p`.`title`,
    `p`.`cityId`,
	`city`.`name`,
	`u`.`userId`,
    `u`.`login`,
    `u`.`firstName`,
    `u`.`lastName`,
    `u`.`sex`,
    `u`.`lastSeen`,
    `h`.`photoId`,
    `h`.`type`,
    `h`.`date`,
    `h`.`path`,
    `h`.`photo200`,
    `h`.`photoMax`,
    `h`.`latitude`,
    `h`.`longitude`
FROM
	`user` `u`,
	`point` `p` LEFT JOIN `city` ON `city`.`cityId` = `p`.`cityId`,
    `photo` `h`
WHERE
	(`p`.`lat` BETWEEN :lat1 AND :lat2) AND
    (`p`.`lng` BETWEEN :lng1 AND :lng2) AND
    `p`.`ownerId` = `u`.`userId`AND
	`h`.`photoId` = `u`.`photoId`
ORDER BY
	`pointId` DESC

SQL;

			$sql = $code . " LIMIT " . $this->offset . ",". $this->count;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":lat1" => $this->lat1,
				":lat2" => $this->lat2,
				":lng1" => $this->lng1,
				":lng2" => $this->lng2
			]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$users = [];
			$points = [];

			foreach ($items as $item) {
				$points[] = new Sight($item);
				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}
			}

			$list = new ListCount(sizeOf($items), $points);
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
	COUNT(`point`.`pointId`) AS `count`
FROM
	`city` LEFT JOIN `point` ON `city`.`cityId` = `point`.`cityId`
WHERE
	(`city`.`lat` BETWEEN :lat1 AND :lat2) AND (`city`.`lng` BETWEEN :lng1 AND :lng2) {$additional}
GROUP BY `point`.`cityId` 
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
			$list->putCustomData("filtered", $needChildren ? "add" : "important");
			return $list;
		}
	}