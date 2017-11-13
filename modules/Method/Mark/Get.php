<?php

	namespace Method\Mark;

	use Model\ListCount;
	use Method\APIPublicMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Get extends APIPublicMethod {

		/** @var int */
		protected $count = 200;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return ListCount
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `mark` LIMIT " . ((int) $this->offset) . "," . ((int) $this->count));
			$items = $db->query($sql, DatabaseResultType::ITEMS);

			$sql = "SELECT COUNT(*) FROM `mark`";
			$count = $db->query($sql, DatabaseResultType::COUNT);

			$items = parseItems($items, "\\Model\\Mark");

			return new ListCount($count, $items);
		}
	}