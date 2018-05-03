<?php

	namespace Method\Point;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение всех мест, в которых был (или хочет побывать) текущий пользователь
	 * @package Method\Point
	 */
	class GetVisited extends APIPublicMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			if (!$main->isAuthorized()) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT `pointId`, `state` FROM `pointVisit`, `authorize` WHERE `authorize`.`authKey` = ? AND `authorize`.`userId` = `pointVisit`.`userId`");
			$stmt->execute([$main->getAuthKey()]);

			$result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($result as &$i) {
				$i = (int) $i;
			}
			return $result;
		}
	}