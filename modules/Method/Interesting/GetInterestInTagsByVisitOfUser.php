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

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
SELECT
	`pm`.`markId`,
    COUNT(`pm`.`markId`) AS `visited`,
    (SELECT COUNT(*) FROM `pointMark` WHERE `markId` = `pm`.`markId`) AS `all`
FROM
	`pointVisit` `pv` RIGHT JOIN `pointMark` `pm` ON `pv`.`pointId` = `pm`.`pointId`
WHERE
	`pv`.`userId` = :userId
GROUP BY
	`markId`
SQL;

			$stmt = $main->makeRequest($sql);

			$stmt->execute([":userId" => $main->getUser()->getId()]);

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$result = array_map(function($item) {
				$res =  [
					"markId" => (int) $item["markId"],
					"visited" => (int) $item["visited"],
					"all" => (int) $item["all"]
				];

				$res["percent"] = $res["visited"] / $res["all"];

				return $res;
			}, $result);

			usort($result, function($a, $b) {
				return $a["percent"] - $b["percent"] < 0 ? 1 : -1;
			});

			return $result;
		}

	}