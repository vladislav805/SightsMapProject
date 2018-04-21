<?php

	namespace Method\Point;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Point;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	/**
	 * Возвращение популярных мест (по числу посещений)
	 * TODO: оптимизировать как в Points|Get
	 * @package Method\Point
	 */
	class GetPopular extends APIPublicMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 */
		public function resolve(IController $main, DatabaseConnection $db) {

			$sql = <<<SQL
SELECT
	COUNT(`pointVisit`.`pointId`) AS `count`,
	`point`.`ownerId`,
	`point`.`pointId`,
	`point`.`lat`,
	`point`.`lng`,
	`point`.`title`,
	`point`.`description`,
	`point`.`dateCreated`,
	`point`.`dateUpdated`,
	`point`.`isVerified`
FROM
	`point`,
	`pointVisit`
WHERE
	`pointVisit`.`pointId` = `point`.`pointId` AND `pointVisit`.`state` = 1
GROUP BY
	`pointId`
ORDER BY
	`count` DESC
LIMIT 50
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute();
			$itemsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$items = parseItems($itemsRaw, "\\Model\\Point");
			$stats = [];

			$list = new ListCount(-1, $items);

			$i = 0;
			$userIds = array_unique(array_map(function(Point $placemark) use (&$pointIds, &$i, &$stats, &$itemsRaw) {
				$pointIds[] = $placemark->getId();
				$stats[] = ["pointId" => $itemsRaw[$i]["pointId"], "count" => $itemsRaw[$i]["count"]];
				$i++;
				return $placemark->getOwnerId();
			}, $list->getItems()));

			//$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));
			$marks = $main->perform(new GetMarksForPoints((new Params())->set("pointIds", $pointIds)));
			$visited = $main->perform(new GetVisited(new Params));

			$user = $main->getUser();

			$items = $list->getItems();
			array_walk($items, function(Point $placemark) use ($user, $marks, $visited) {
				$user && $placemark->setAccessByCurrentUser($user);
				if (isset($marks[$placemark->getId()])) {
					$placemark->setMarks($marks[$placemark->getId()]);
				}
				$placemark->setVisitState(isset($visited[$placemark->getId()]) ? $visited[$placemark->getId()] : 0);
				return $placemark;
			});

			//$list->putCustomData("users", $users);
			$list->putCustomData("stats", $stats);

			return $list;
		}
	}