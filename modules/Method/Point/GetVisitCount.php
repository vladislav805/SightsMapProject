<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Params;
	use Model\Point;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	/**
	 * Получение количества визитов и желаний посетить (из всех пользователей) конкретного места
	 * @package Method\Point
	 */
	class GetVisitCount extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$sql = "SELECT `state`, COUNT(`id`) AS `count` FROM `pointVisit` WHERE `pointId` = ? GROUP BY `state`";

			$stmt = $main->makeRequest($sql);
			$stmt->execute([$this->pointId]);
			$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

			/*$d = [0, 0];

			foreach ($rows as $row) {
				$d[$row["state"] - 1] = (int) $row["count"];
			}*/



			return [
				"visited" => isset($rows[1]) ? (int) $rows[1] : 0,
				"desired" => isset($rows[2]) ? (int) $rows[2] : 0
			];
		}
	}