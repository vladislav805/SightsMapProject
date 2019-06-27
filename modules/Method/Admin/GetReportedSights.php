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

					$user = get_object_of_prefix($item, "reported");

					$users[$item["userId"]] = new User($user);
				}
			}

			return (new ListCount(sizeOf($items), $items))->putCustomData("users", array_values($users));
		}
	}