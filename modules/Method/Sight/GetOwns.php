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
	use Model\User;
	use PDO;

	/**
	 * Получение мест конкретного пользователя
	 * @package Method\Point
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

			$list = $this->getPoints($main);

			$pointIds = array_map(function(Sight $placemark) {
				return $placemark->getId();
			}, $list->getItems());

			$marks = $main->perform(new GetByPoints((new Params())->set("sightIds", $pointIds)));

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
		public function getPoints($main) {

			$count = "SELECT COUNT(DISTINCT `point`.`pointId`) AS `count` FROM `point` WHERE `point`.`ownerId` = :oid";

			$stmt = $main->makeRequest($count);
			$stmt->execute([":oid" => $this->ownerId]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$countResults = (int) $row["count"];


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
	`p`.`ownerId` = :oid AND
    `p`.`ownerId` = `u`.`userId`AND
    `u`.`userId` = `h`.`ownerId` AND
	`h`.`type` = 2 AND
	`h`.`photoId` >= ALL (
		SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `u`.`userId` AND `photo`.`type` = 2
	)
ORDER BY
	`pointId` DESC
SQL;

			$sql = $code . " LIMIT " . $this->offset . "," . $this->count;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":oid" => $this->ownerId]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$points = [];
			$users = [];

			foreach ($items as $item) {
				$points[] = new Sight($item);
				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}
			}

			return (new ListCount($countResults, $points))->putCustomData("users", array_values($users));
		}

	}