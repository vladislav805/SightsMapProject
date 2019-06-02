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

		const KEY_TOTAL = "api_s_gc_total";
		const KEY_VERIFIED = "api_s_gc_verified";
		const KEY_ARCHIVED = "api_s_gc_archived";

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {

			// without redis 0.00088810920715332
			// with redis    0.00021505355834961

			$redis = $main->getRedis();

			if ($redis->get(self::KEY_TOTAL)) {
				$result = [
					"total" => $redis->get(self::KEY_TOTAL),
					"verified" => $redis->get(self::KEY_VERIFIED),
					"archived" => $redis->get(self::KEY_ARCHIVED)
				];
			} else {


				$count = <<<SQL
SELECT 
       (SELECT COUNT(*) FROM `sight`) AS `total`,
       (SELECT COUNT(*) FROM `sight` WHERE `sight`.`isVerified` = 1) AS `verified`,
       (SELECT COUNT(*) FROM `sight` WHERE `sight`.`isArchived` = 1) AS `archived`
SQL;

				$stmt = $main->makeRequest($count);

				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);

				$exOptions = ["EX" => 600];
				$redis->set(self::KEY_TOTAL, $result["total"], $exOptions);
				$redis->set(self::KEY_VERIFIED, $result["verified"], $exOptions);
				$redis->set(self::KEY_ARCHIVED, $result["archived"], $exOptions);
			}

			return [
				"total" => (int) $result["total"],
				"verified" => (int) $result["verified"],
				"archived" => (int) $result["archived"]
			];
		}

	}