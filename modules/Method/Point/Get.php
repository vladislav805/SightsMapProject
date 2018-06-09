<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\Mark\GetByPoints;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Point;
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

		public function __construct($r) {
			parent::__construct($r);
		}

		/**
		 * @param IController $main
		 * @return ListCount
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!($this->lat1 && $this->lat2 && $this->lng1 && $this->lng2)) {
				throw new APIException(ERROR_NO_PARAM, $_REQUEST);
			}

			$lat1 = min($this->lat1, $this->lat2);
			$lat2 = max($this->lat1, $this->lat2);
			$lng1 = min($this->lng1, $this->lng2);
			$lng2 = max($this->lng1, $this->lng2);

			$this->count = min($this->count, self::MAX_LIMIT);
			$this->offset = min(0, $this->offset);

			$this->lat1 = $lat1;
			$this->lat2 = $lat2;
			$this->lng1 = $lng1;
			$this->lng2 = $lng2;

			$list = $this->getPointsInArea($main);

			$pointIds = array_map(function(Point $placemark) {
				return $placemark->getId();
			}, $list->getItems());

			$marks = $main->perform(new GetByPoints((new Params())->set("pointIds", $pointIds)));

			if ($main->isAuthorized()) {
				$user = $main->getUser();
				$visited = $main->perform(new GetVisited(new Params));
			} else {
				$visited = null;
				$user = null;
			}

			$items = $list->getItems();
			array_walk($items, function(Point $placemark) use ($user, $marks, $visited) {
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
    `u`.`userId` = `h`.`ownerId` AND
	`h`.`type` = 2 AND
	`h`.`photoId` >= ALL (
		SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `u`.`userId` AND `photo`.`type` = 2
	)
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

			$points = [];
			$users = [];

			foreach ($items as $item) {
				$points[] = new Point($item);
				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}
			}

			return (new ListCount(sizeOf($items), $points))->putCustomData("users", array_values($users));
		}

/*
SELECT
	DISTINCT `p`.`pointId`,
    `p`.`ownerId`,
	`p`.`lat`,
    `p`.`lng`,
    `p`.`dateCreated`,
    `p`.`dateUpdated`,
    `p`.`isVerified`,
    `p`.`description`,
    `p`.`title`,
	`u`.`userId`,
    `u`.`login`,
    `u`.`firstName`,
    `u`.`lastName`,
    `u`.`sex`,
    `u`.`lastSeen` , `pv`.`state`
FROM
	`user` `u`, `point` `p` , `pointVisit` `pv`
WHERE
	(`p`.`lat` BETWEEN 59.99159640457564 AND 60.02675051471252) AND
    (`p`.`lng` BETWEEN 30.09946451782227 AND 30.293098551025384) AND
    `p`.`ownerId` = `u`.`userId` AND `p`.`pointId` = `pv`.`pointId`
*/

	}