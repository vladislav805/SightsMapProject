<?php

	namespace Method\Photo;

	use APIPrivateMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @return mixed
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			$id = $main->getSession()->getUserId();

			if ($id < 100) {
				return true;
			}

			$sql = sprintf("SELECT COUNT(*) FROM `photo` WHERE `ownerId` = '%d' AND `date` > '%d'", $id, time() - self::LIMIT_TIME);
			$count = $db->query($sql, DatabaseResultType::COUNT);
			return $count <= self::LIMIT_UPLOADS_PER_TIME;
		}
	}