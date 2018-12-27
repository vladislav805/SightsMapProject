<?php

	namespace Method\Interesting;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	/**
	 * Вычисление интересов пользователя по посещенным местам
	 * @package Method\Point
	 */
	class GetInterestInTagsByVisitOfUser extends APIPrivateMethod {

		const K_visited = 1;
		const K_desired = 1.5;

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
SELECT
	`pm`.`markId`,
    `pv`.`state`,
    COUNT(`pm`.`markId`) AS `count`,
    (SELECT COUNT(*) FROM `pointMark` WHERE `markId` = `pm`.`markId`) AS `all`
FROM
	`pointVisit` `pv` RIGHT JOIN `pointMark` `pm` ON `pv`.`pointId` = `pm`.`pointId`
WHERE
	`pv`.`userId` = :userId
GROUP BY
	`markId`, `state`
SQL;

			$stmt = $main->makeRequest($sql);

			$stmt->execute([":userId" => $main->getUser()->getId()]);

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$stat = [];
			$keys = [null, "visited", "desired"];

			foreach ($result as $item) {
				$id = (int) $item["markId"];
				if (!isset($stat[$id])) {
					$stat[$id] = [
						"markId" => $id,
						"all" => (int) $item["all"],
						"visited" => 0,
						"desired" => 0
					];
				}

				$stat[$id][$keys[$item["state"]]] = (int) $item["count"];
			}

			$stat = array_values($stat);

			$stat = array_map(function($item) {
				/**
				 * K - коэффициент значимости желаемых мест
				 *     visited * Kп + desired * Kд
				 * F = ---------------------------
				 *             all * Kд * Kп
				 */
				$item["percent"] = ($item["visited"] * self::K_visited + $item["desired"] * self::K_desired) / ($item["all"] * self::K_visited * self::K_desired);
				return $item;
			}, $stat);

			usort($stat, function($a, $b) {
				return $a["percent"] - $b["percent"] < 0 ? 1 : -1;
			});

			return $stat;
		}

	}