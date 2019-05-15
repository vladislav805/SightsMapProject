<?php

	namespace Method\Interesting;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	/**
	 * Вычисление интересов пользователя по поставленному рейтингу
	 * @package Method\Point
	 */
	class GetInterestInTagsByRatingOfUser extends APIPrivateMethod {

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
SELECT
	`pm`.`markId`,
    AVG(`rt`.`rate`) AS `value`
FROM
	`rating` `rt`
    	RIGHT JOIN `pointMark` `pm` ON `rt`.`pointId` = `pm`.`pointId`
WHERE
	`rt`.`userId` = :userId
GROUP BY
	`markId`
SQL;

			$stmt = $main->makeRequest($sql);

			$stmt->execute([":userId" => $main->getUser()->getId()]);

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$result = array_map(function($item) {
				$res =  [
					"markId" => (int) $item["markId"],
					"value" => (double) $item["value"]
				];

				return $res;
			}, $result);

			usort($result, function($a, $b) {
				return $a["value"] - $b["value"] < 0 ? 1 : -1;
			});

			return $result;
		}

	}