<?php

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Sight;
	use PDO;

	/**
	 * Получение базовой информации о нескольких местах одновременно по их идентификаторам
	 * @package Method\Sight
	 */
	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $sightIds;

		public function __construct($request) {
			parent::__construct($request);

			if (is_string($this->sightIds)) {
				$this->sightIds = explode(",", (string) $this->sightIds);
			}

			$this->sightIds = array_values(array_filter($this->sightIds));
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

			$stmt = $main->makeRequest("
SELECT
	`p`.*,
	GROUP_CONCAT(DISTINCT `pm`.`markId`) AS `markIds`,
	`c`.`name`, 
	`ph`.`ownerId` AS `photoOwnerId`,
	`ph`.`photoId`,
	`ph`.`date` AS `photoDate`,
	`ph`.`path`,
	`ph`.`type`,
	`ph`.`photo200`,
	`ph`.`photoMax`,
	getRatedSightByUser(:uid, `p`.`sightId`) AS `rated`
FROM
	`sight` `p`
		LEFT JOIN `city` `c` ON `p`.`cityId` = `c`.`cityId`
		LEFT JOIN `sightPhoto` `pp` ON `p`.`sightId` = `pp`.`sightId`
		LEFT JOIN `photo` `ph` ON `pp`.`photoId` = `ph`.`photoId`
		LEFT JOIN `sightMark` `pm` ON  `p`.`sightId` = `pm`.`sightId` 
		LEFT JOIN `sightVisit` `pv` ON `p`.`sightId` = `pv`.`sightId` AND `pv`.`userId` = :uid
WHERE
	`p`.`sightId` IN ('" . join("','", $sightIds) . "')
GROUP BY `p`.`sightId`
");
			$stmt->execute([":uid" => $main->getUser()->getId()]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/** @var Sight[] $items */
			$items = parseItems($items, "\\Model\\Sight");

			foreach ($items as $item) {
				$item->setAccessByCurrentUser($main->getUser());
			}

			return $items;
		}
	}