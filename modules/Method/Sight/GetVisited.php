<?php

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение всех мест, в которых был (или хочет побывать) текущий пользователь
	 * @package Method\Point
	 */
	class GetVisited extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			if (!$main->isAuthorized()) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT `pointId`, `state` FROM `pointVisit` WHERE ? = `pointVisit`.`userId`");
			$stmt->execute([$main->getUser()->getId()]);

			$result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($result as &$i) {
				$i = (int) $i;
			}
			return $result;
		}
	}