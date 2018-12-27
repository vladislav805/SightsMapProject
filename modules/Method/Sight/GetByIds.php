<?php

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Sight;
	use PDO;

	/**
	 * Получение информации о нескольких местах одновременно по их идентификаторам
	 * @package Method\Point
	 */
	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $sightIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->sightIds = array_values(array_filter(explode(",", (string) $this->sightIds)));
		}

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$sightIds = array_unique(array_map("intval", $this->sightIds));

			if (!sizeOf($sightIds)) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT * FROM `point` LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId` WHERE `pointId` IN ('" . join("','", $sightIds) . "')");
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/** @var Sight[] $items */
			$items = parseItems($items, "\\Model\\Sight");

			foreach ($items as $item) {
				$item->setAccessByCurrentUser($main->getUser());
			}

			return $items;
		}
	}