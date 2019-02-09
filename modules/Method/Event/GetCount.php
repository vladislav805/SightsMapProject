<?php

	namespace Method\Event;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	class GetCount extends APIPrivateMethod {

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
			$userId = $main->getSession()->getUserId();

			$sql = <<<SQL
SELECT
	COUNT(*)
FROM
	`event`, `authorize`
WHERE
	`ownerUserId` = `authorize`.`userId` AND `authorize`.`authKey` = :authKey AND `isNew` = 1
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":authKey" => $main->getAuthKey()]);

			list($count) = $stmt->fetch(PDO::FETCH_NUM);

			return (int) $count;
		}
	}