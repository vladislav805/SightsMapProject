<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;
	use Model\Sight;
	use PDO;

	/**
	 * Получение информации об одном месте по его идентификатору
	 * @package Method\Point
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
	`point`.*,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
    `photo`.`type`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	getRatedSightByUser(:userId, `point`.`pointId`) AS `rated`
FROM
	`point`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
		LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
WHERE
	`point`.`pointId` = :sightId OR `point`.`parentId` = :sightId
GROUP BY
	`point`.`pointId`
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
				if ($i["pointId"] == $this->sightId) {
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

			if ($main->isAuthorized()) {
				$visited = $main->perform(new GetVisited(new Params));

				$hasVisit = function(Sight $item) use ($visited) {
					return isset($visited[$item->getId()]) ? $visited[$item->getId()] : 0;
				};

				$item->setVisitState($hasVisit($item));

				if ($parent) {
					$parent->setVisitState($hasVisit($parent));
				}

				if ($child) {
					$child->setVisitState($hasVisit($child));
				}
			}

			if ($child) {
				$item->setChild($child);
			}

			if ($parent) {
				$item->setParent($parent);
			}

			$stmt = $main->makeRequest("SELECT `markId` FROM `pointMark` WHERE `pointId` = ?");
			$stmt->execute([$this->sightId]);
			$res = $stmt->fetchAll(PDO::FETCH_NUM);

			$res = array_map(function($item) {
				return (int) $item[0];
			}, $res);

			$item->setMarks($res);

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
	`point`.*,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
    `photo`.`type`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	getRatedSightByUser(:userId, `point`.`pointId`) AS `rated`
FROM
	`point`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
		LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
WHERE
	`point`.`pointId` = :sightId
GROUP BY
	`point`.`pointId`
LIMIT 1
SQL;

			$parentSt = $main->makeRequest($sql);
			$parentSt->execute([":sightId" => $item->getParentId(), ":userId" => $main->isAuthorized() ? $main->getUser()->getId() : 0]);
			$parentObj = $parentSt->fetch(PDO::FETCH_ASSOC);
			$parent = new Sight($parentObj);

			return $parent;
		}
	}