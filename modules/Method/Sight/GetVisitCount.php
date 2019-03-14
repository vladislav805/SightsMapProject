<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use PDO;

	/**
	 * Получение количества визитов и желаний посетить (из всех пользователей) конкретного места
	 * @package Method\Sight
	 */
	class GetVisitCount extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId is not specified");
			}

			$sql = "SELECT `state`, COUNT(`id`) AS `count` FROM `pointVisit` WHERE `pointId` = ? GROUP BY `state`";

			$stmt = $main->makeRequest($sql);
			$stmt->execute([$this->sightId]);
			$rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

			return [
				"visited" => (int) ($rows[1] ?? 0),
				"desired" => (int) ($rows[2] ?? 0)
			];
		}
	}