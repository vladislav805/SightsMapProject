<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * @package Method\User
	 */
	class GetCityExperts extends APIPublicMethod {

		/** @var int */
		protected $cityId;

		/** @var boolean */
		protected $onlyVerified;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			$cond = [];

			if ($this->cityId) {
				$cond[] = "`sight`.`cityId` = " . $this->cityId;
			}

			if ($this->onlyVerified) {
				$cond[] = "`sight`.`isVerified` = 1";
			}


			$cond = sizeOf($cond) ? "WHERE " . join(" AND ", $cond) : "";

			$sql = <<<SQL
SELECT
    COUNT(*) AS `sightsCount`,
    `user`.*,
    `city`.`name`,
    `photo`.*,
    getUserRating(`user`.`userId`) AS `rating`
FROM
	`sight`
	    LEFT JOIN `user` ON `sight`.`ownerId` = `user`.`userId`
		LEFT JOIN `city` ON `user`.`cityId` = `city`.`cityId`
		LEFT JOIN `photo` ON `photo`.`photoId` = `user`.`photoId`
{$cond}
GROUP BY
	`sight`.`ownerId`
ORDER BY
	`sightsCount` DESC
SQL;
			$stmt = $main->makeRequest($sql);
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$users = parseItems($items, "\\Model\\User");
			$values = array_map(function($i) {
				return ["userId" => (int) $i["userId"], "count" => (int) $i["sightsCount"]];
			}, $items);

			return (new ListCount(sizeOf($users), $users))->putCustomData("counts", $values);
		}
	}