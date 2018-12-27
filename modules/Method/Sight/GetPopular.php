<?php

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Method\Mark\GetByPoints;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Sight;
	use PDO;

	/**
	 * Возвращение популярных мест (по числу посещений)
	 * TODO: оптимизировать как в Points|Get
	 * @package Method\Point
	 */
	class GetPopular extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {

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

			$items = parseItems($itemsRaw, "\\Model\\Sight");
			$stats = [];

			$list = new ListCount(-1, $items);

			$i = 0;
			array_map(function(Sight $placemark) use (&$pointIds, &$i, &$stats, &$itemsRaw) {
				$pointIds[] = $placemark->getId();
				$stats[] = ["pointId" => $itemsRaw[$i]["pointId"], "count" => $itemsRaw[$i]["count"]];
				$i++;
				return $placemark->getOwnerId();
			}, $list->getItems());

			//$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));
			$marks = $main->perform(new GetByPoints((new Params())->set("pointIds", $pointIds)));
			$visited = $main->perform(new GetVisited(new Params));

			$user = $main->getUser();

			$items = $list->getItems();
			array_walk($items, function(Sight $placemark) use ($user, $marks, $visited) {
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