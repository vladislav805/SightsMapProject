<?php

	namespace Method\Point;

	use Model\IController;
	use Method\APIPublicMethod;
	use Model\Point;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	/**
	 * Получение информации о нескольких местах одновременно по их идентификаторам
	 * @package Method\Point
	 */
	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $pointIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->pointIds = array_values(array_filter(explode(",", (string) $this->pointIds)));
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$pointIds = array_unique(array_map("intval", $this->pointIds));

			if (!sizeOf($pointIds)) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT * FROM `point` WHERE `pointId` IN ('" . join("','", $pointIds) . "')");
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/** @var Point[] $items */
			$items = parseItems($items, "\\Model\\Point");

			foreach ($items as $item) {
				$item->setAccessByCurrentUser($main->getUser());
			}

			return $items;
		}
	}