<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Sight;
	use PDO;

	/**
	 * Получение информации об одном месте по его идентификатору
	 * @package Method\Sight
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/**
		 * @param IController $main
		 * @return Sight
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
SELECT
	`sight`.*,
	IFNULL(`sightVisit`.`state`, 0) AS `visitState`,
	GROUP_CONCAT(DISTINCT `sightMark`.`markId`) AS `markIds`,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
	`photo`.`type`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	`sightPhoto`.`orderId`,
	getRatedSightByUser(:userId, `sight`.`sightId`) AS `rated`
FROM
	`sight`
		LEFT JOIN `city` ON `city`.`cityId` = `sight`.`cityId`
		LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId`
		LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `sightMark` ON  `sightMark`.`sightId` = `sight`.`sightId`
		LEFT JOIN `sightVisit` ON `sightVisit`.`sightId` = `sight`.`sightId` AND `sightVisit`.`userId` = :userId
WHERE
	`sight`.`sightId` = :sightId OR `sight`.`parentId` = :sightId
GROUP BY
	`sight`.`sightId`,
	`sightPhoto`.`orderId` DESC
LIMIT 2
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":sightId" => $this->sightId, ":userId" => $main->isAuthorized() ? $main->getUser()->getId() : 0]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (!$items) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND, null, "sight not found");
			}

			/** @var Sight|array $item */
			$item = null;

			/** @var Sight|array|null $parent */
			$parent = null;

			/** @var Sight|array|null $child */
			$child = null;

			foreach ($items as $i) {
				if ($i["sightId"] == $this->sightId) {
					$item = $i;
				} else {
					$child = $i;
				}
			}

			if (!$item) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND, null, "sight not found");
			}

			$item = new Sight($item);

			if ($child) {
				$child = new Sight($child);
			}

			if ($item->getParentId()) {
				$parent = $this->findParent($main, $item);
			}

			if ($child) {
				$item->setChild($child);
			}

			if ($parent) {
				$item->setParent($parent);
			}

			($user = $main->getUser()) && $item->setAccessByCurrentUser($user);

			return $item;
		}

		/**
		 * @param IController $main
		 * @param Sight $item
		 * @return Sight
		 */
		private function findParent($main, $item) {
			$sql = <<<SQL
SELECT
	`sight`.*,
	`city`.`name`,
    IFNULL(`sightVisit`.`state`, 0) AS `visitState`,
    GROUP_CONCAT(DISTINCT `sightMark`.`markId`) AS `markIds`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
    `photo`.`type`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	getRatedSightByUser(:userId, `sight`.`sightId`) AS `rated`
FROM
	`sight`
		LEFT JOIN `city` ON `city`.`cityId` = `sight`.`cityId`
		LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId`
		LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `sightMark` ON  `sightMark`.`sightId` = `sight`.`sightId`
		LEFT JOIN `sightVisit` ON `sightVisit`.`sightId` = `sight`.`sightId` AND `sightVisit`.`userId` = :userId
WHERE
	`sight`.`sightId` = :sightId
GROUP BY
	`sight`.`sightId`
LIMIT 1
SQL;

			$parentSt = $main->makeRequest($sql);
			$parentSt->execute([":sightId" => $item->getParentId(), ":userId" => $main->isAuthorized() ? $main->getUser()->getId() : 0]);
			$parentObj = $parentSt->fetch(PDO::FETCH_ASSOC);
			$parent = new Sight($parentObj);

			return $parent;
		}
	}