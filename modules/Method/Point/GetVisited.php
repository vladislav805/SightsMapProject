<?php

	namespace Method\Point;

	use APIPublicMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetVisited extends APIPublicMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			if (!$main->getSession()) {
				return [];
			}

			$sql = sprintf("SELECT `pointId`, `state` FROM `pointVisit` WHERE `userId` = '%d'", $main->getSession()->getUserId());

			$items = $db->query($sql, DatabaseResultType::ITEMS);

			$ids = [];

			foreach ($items as $item) {
				$ids[$item["pointId"]] = (int) $item["state"];
			}

			return $ids;
		}
	}