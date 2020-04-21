<?php

	namespace Method\Internal;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Статистика сайта
	 * @package Method\Sight
	 */
	class GetCounts extends APIPublicMethod {

		const KEY_TOTAL = "api_s_gc_total";
		const KEY_VERIFIED = "api_s_gc_verified";
		const KEY_ARCHIVED = "api_s_gc_archived";
		const KEY_USERS = "api_s_gc_users";
		const KEY_VISITED = "api_s_gc_visited";

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$redis = $main->getRedis();

			if ($redis->get(self::KEY_TOTAL)) {
				$result = [
					"total" => $redis->get(self::KEY_TOTAL),
					"verified" => $redis->get(self::KEY_VERIFIED),
					"archived" => $redis->get(self::KEY_ARCHIVED),
					"users" => $redis->get(self::KEY_USERS),
					"visited" => $redis->get(self::KEY_VISITED),
				];
			} else {


				$count = <<<SQL
SELECT 
       (SELECT COUNT(*) FROM `sight`) AS `total`,
       (SELECT COUNT(*) FROM `sight` WHERE `sight`.`isVerified` = 1) AS `verified`,
       (SELECT COUNT(*) FROM `sight` WHERE `sight`.`isArchived` = 1) AS `archived`,
       (SELECT COUNT(*) FROM `user`) AS `users`,
       (SELECT COUNT(*) FROM `sightVisit`) AS `visited` 
SQL;

				$stmt = $main->makeRequest($count);

				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);

				$exOptions = ["EX" => 600];
				$redis->set(self::KEY_TOTAL, $result["total"], $exOptions);
				$redis->set(self::KEY_VERIFIED, $result["verified"], $exOptions);
				$redis->set(self::KEY_ARCHIVED, $result["archived"], $exOptions);
				$redis->set(self::KEY_USERS, $result["users"], $exOptions);
				$redis->set(self::KEY_VISITED, $result["visited"], $exOptions);
			}

			return [
				"total" => (int) $result["total"],
				"verified" => (int) $result["verified"],
				"archived" => (int) $result["archived"],
				"users" => (int) $result["users"],
				"visited" => (int) $result["visited"],
			];
		}

	}