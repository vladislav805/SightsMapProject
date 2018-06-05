<?php

	namespace Method\Mark;

	use Model\IController;
	use Method\APIPublicMethod;
	use PDO;

	/**
	 * Получение идентификаторов категорий из БД для массива меток
	 * @package Method\Point
	 */
	class GetByPoints extends APIPublicMethod {

		/** @var int[] */
		protected $pointIds;

		/**
		 * GetMarks constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return array[]
		 */
		public function resolve(IController $main) {
			if (!sizeOf($this->pointIds)) {
				return [];
			}

			$pointIds = join(",", $this->pointIds);

			$stmt = $main->makeRequest("SELECT `pointId`, `markId` FROM `pointMark` WHERE `pointId` IN (" . $pointIds . ")");
			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

			foreach ($res as &$row) {
				$l = [];
				foreach ($row as $item) {
					$l[] = (int) $item["markId"];
				}
				$row = $l;
			}

			return $res;
		}
	}