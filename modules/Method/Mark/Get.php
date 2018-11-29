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

		/** @var boolean */
		protected $needCount = false;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {

			$sql = $this->needCount
				? "SELECT `mark`.*, COUNT(`pm`.`id`) AS `count` FROM `mark` LEFT JOIN `pointMark` `pm` ON `mark`.`markId` = `pm`.`markId` GROUP BY `mark`.`markId`"
				: "SELECT * FROM `mark`";

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Mark");

			return new ListCount(sizeOf($items), $items);
		}
	}