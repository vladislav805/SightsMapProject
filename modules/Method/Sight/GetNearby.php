<?

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * Class GetNearby
	 * https://stackoverflow.com/questions/6868057/large-mysql-db-21mm-records-with-location-data-each-location-has-lat-and-lon
	 * @package Method\Sight
	 */
	class GetNearby extends APIPublicMethod {

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

		/** @var float: distance in m */
		protected $distance;

		/** @var int */
		protected $count = 20;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->lat || !$this->lng) {
				throw new APIException(ErrorCode::NO_PARAM, null, "lat or lng is not specified");
			}

			if (!isCoordinate($this->lat, $this->lng)) {
				throw new APIException(ErrorCode::INVALID_COORDINATES, null, "lat or lng is not coordinates");
			}

			if (!inRange($this->distance, 0, 3000)) {
				$this->distance = 500;
			}

			if (!inRange($this->count, 1, 100)) {
				$this->count = 20;
			}

			$sql = <<<SQL
SELECT
	`sight`.*,
    IFNULL(`sightVisit`.`state`, 0) AS `visitState`,
    GROUP_CONCAT(DISTINCT `sightMark`.`markId`) AS `markIds`,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`type`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
	`photo`.`photo200`,
	`photo`.`photoMax`,
	floor(
		6371000 * acos(
			cos(radians($this->lat)) * cos(radians(`sight`.`lat`)) * cos(radians(`sight`.`lng`) - radians($this->lng)) + sin(radians($this->lat)) * sin(radians(`sight`.`lat`))
		)
    ) AS `distance`
FROM
	`sight`
    	LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId`
		LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `city` ON `city`.`cityId` = `sight`.`cityId`
		LEFT JOIN `sightVisit` ON `sightVisit`.`sightId` = `sight`.`sightId` AND `sightVisit`.`userId` = :uid
		LEFT JOIN `sightMark` ON  `sightMark`.`sightId` = `sight`.`sightId`
WHERE
	`sight`.`lat` > :lat - 0.04
		AND
	`sight`.`lat` < :lat + 0.04
        AND
    `sight`.`lng` > :lng - 0.04
        AND
    `sight`.`lng` < :lng + 0.04
GROUP BY
	`sight`.`sightId`
HAVING
	`distance` BETWEEN 0.001 AND :distance
ORDER BY
	`distance`
LIMIT $this->count
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":distance" => $this->distance,
				":lat" => $this->lat,
				":lng" => $this->lng,
				":uid" => $main->isAuthorized() ? $main->getUser()->getId() : 0
			]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$sights = parseItems($items, "\\Model\\Sight");

			$list = new ListCount(sizeof($sights), $sights);

			$distances = [];

			foreach ($items as $item) {
				$distances[] = [
					"sightId" => (int) $item["sightId"],
					"distance" => (double) $item["distance"]
				];
			}

			$list->putCustomData("distances", $distances);
			return $list;
		}
	}