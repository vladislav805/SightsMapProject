<?php

	namespace Method\Mark;

	use Model\ListCount;
	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение всех существующих категорий меток
	 * @package Method\Mark
	 */
	class Get extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `mark`");
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Mark");

			return new ListCount(sizeOf($items), $items);
		}
	}