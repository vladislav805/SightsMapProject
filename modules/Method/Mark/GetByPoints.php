<?php

	namespace Method\Mark;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение идентификаторов категорий из БД для массива меток
	 * @package Method\Point
	 */
	class GetByPoints extends APIPublicMethod {

		/** @var int[] */
		protected $sightIds;

		/**
		 * @param IController $main
		 * @return array[]
		 */
		public function resolve(IController $main) {
			if (!sizeOf($this->sightIds)) {
				return [];
			}

			$pointIds = join(",", $this->sightIds);

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