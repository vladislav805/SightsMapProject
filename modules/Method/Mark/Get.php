<?php

	namespace Method\Mark;

	use Model\ListCount;
	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;
	use tools\DatabaseConnection;

	/**
	 * Получение всех существующих категорий меток
	 * @package Method\Mark
	 */
	class Get extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return ListCount
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$stmt = $main->makeRequest("SELECT * FROM `mark`");
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Mark");

			return new ListCount(sizeOf($items), $items);
		}
	}