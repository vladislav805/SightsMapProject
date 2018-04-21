<?php

	namespace Method\Photo;

	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;
	use tools\DatabaseConnection;

	/**
	 * Проверка на автоматическую загрузку.
	 * @package Method\Photo
	 */
	class CheckFlood extends APIPrivateMethod {

		const LIMIT_TIME = 30 * 60;
		const LIMIT_UPLOADS_PER_TIME = 7;

		/**
		 * CheckFlood constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * Realization of some action
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = <<<SQL
SELECT
	IF(`user`.`userId` > 100, COUNT(*), 0) AS `count`
FROM
	`photo`, `user`, `authorize`
WHERE
	`photo`.`ownerId` = `user`.`userId` AND
	`user`.`userId` = `authorize`.`userId` AND 
	`authorize`.`authKey` = :authKey AND
	`photo`.`date` > :limitDate
SQL;


			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":authKey" => $main->getAuthKey(),
				":limitDate" => time() - self::LIMIT_TIME
			]);
			$count = $stmt->fetch(PDO::FETCH_NUM)[0];
			return $count <= self::LIMIT_UPLOADS_PER_TIME;
		}
	}