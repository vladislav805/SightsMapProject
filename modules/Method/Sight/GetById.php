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
	`photo`.`photo200`,
	`photo`.`photoMax`
FROM
	`point`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
		LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
WHERE
	`point`.`pointId` = :pointId OR `point`.`parentId` = :pointId
GROUP BY
	`point`.`pointId`
LIMIT 2
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":pointId" => $this->sightId]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (!$items) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND, null, "sight not found");
			}

			/** @var Sight|array $item */
			$item = null;

			/** @var Sight|array|null $parent */
			$parent = null;

			foreach ($items as $i) {
				if ($i["pointId"] == $this->sightId) {
					$item = $i;
				} else {
					$parent = $i;
				}
			}

			if (!$item) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND, null, "sight not found");
			}

			$item = new Sight($item);

			if ($parent) {
				$item->setChild(new Sight($parent));
			}

			if ($main->isAuthorized()) {
				$visited = $main->perform(new GetVisited(new Params));

				$item->setVisitState(isset($visited[$item->getId()]) ? $visited[$item->getId()] : 0);
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
	}