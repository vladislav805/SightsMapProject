<?php

	namespace Method\Admin;

	use Method\APIModeratorMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Sight;
	use Model\User;
	use PDO;

	/**
	 * @package Method\Admin
	 */
	class GetReportedSights extends APIModeratorMethod {

		/** @var int */
		protected $offset = 0;

		/** @var int */
		protected $count = 50;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {

			$this->count = (int) $this->count;
			$this->offset = (int) $this->offset;

			$sql = <<<SQL
SELECT
	`reportSight`.*,
    `sight`.*,
    `su`.*,
    `sc`.*,
    `ru`.`userId` AS `reportedUserId`,
    `ru`.`login` AS `reportedLogin`,
    `ru`.`firstName` AS `reportedFirstName`,
    `ru`.`lastName` AS `reportedLastName`,
    `ru`.`cityId` AS `reportedCityId`,
    `ru`.`sex` AS `reportedSex`,
    `ru`.`lastSeen` AS `reportedLastSeen`,
    `uc`.`cityId` AS `reportedCityId`,
    `uc`.`name` AS `reportedName`,
    `uc`.`name4child` AS `reportedName4Child`
FROM
	`reportSight`
    	LEFT JOIN `sight` ON `reportSight`.`sightId` = `sight`.`sightId`
        LEFT JOIN `user` `su` ON `sight`.`ownerId` = `su`.`userId`
        LEFT JOIN `user` `ru` ON `reportSight`.`userId` = `ru`.`userId`
		LEFT JOIN `city` `sc` ON `sight`.`cityId` = `sc`.`cityId`
		LEFT JOIN `city` `uc` ON `su`.`cityId` = `uc`.cityId
LIMIT {$this->offset}, {$this->count}
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$items = [];
			$users = [];

			foreach ($data as $item) {
				$items[] = new Sight($item);

				if (!isset($users[$item["userId"]])) {
					$users[$item["userId"]] = new User($item);
				}

				if (!isset($users[$item["reportedUserId"]])) {

					$u = [];
					foreach ($item as $key => $value) {
						if (strpos($key, "reported") === 0) {
							$str_len = strlen($key);
							$key = substr($key, 8, $str_len);
							$key = strtolower(substr($key, 0, 1)) . substr($key, 1, $str_len - 8);
							$u[$key] = $value;
							var_dump($key);
						}
					}

					$users[$item["userId"]] = new User($u);
				}
			}

			return (new ListCount(sizeOf($items), $items))->putCustomData("users", array_values($users));
		}
	}