<?php

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение количества меток в базе данных
	 * @package Method\Sight
	 */
	class GetCounts extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$count = <<<SQL
SELECT 
       (SELECT COUNT(*) FROM `point`) AS `total`,
       (SELECT COUNT(*) FROM `point` WHERE `point`.`isVerified` = 1) AS `verified`,
       (SELECT COUNT(*) FROM `point` WHERE `point`.`isArchived` = 1) AS `archived`
SQL;

			$stmt = $main->makeRequest($count);

			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			return [
				"total" => (int) $result["total"],
				"verified" => (int) $result["verified"],
				"archived" => (int) $result["archived"]
			];
		}

	}