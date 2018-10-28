<?

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * Class GetNearby
	 * https://stackoverflow.com/questions/6868057/large-mysql-db-21mm-records-with-location-data-each-location-has-lat-and-lon
	 * @package Method\Point
	 */
	class GetNearby extends APIPublicMethod {

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

		/** @var float: distance in km */
		protected $distance;

		/** @var int */
		protected $count = 20;

		/**
		 * GetNearby constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

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

			if (!inRange($this->distance, 0, 2)) {
				$this->distance = .5;
			}

			if (!inRange($this->count, 1, 100)) {
				$this->count = 20;
			}

			$sql = <<<SQL
SELECT
	`point`.*,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
	`photo`.`photo200`,
	`photo`.`photoMax`, (
		6371 * acos(
			cos(radians($this->lat)) * cos(radians(`lat`)) * cos(radians(`lng`) - radians($this->lng)) + sin(radians($this->lat)) * sin(radians(`lat`))
		)
    ) AS `distance`
FROM
	`point`
    	LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
WHERE
	`lat` > $this->lat - 0.5
		AND
	`lat` < $this->lat + 0.5
        AND
    `lng` > $this->lng - 0.5
        AND
    `lng` < $this->lng + 0.5
GROUP BY
	`point`.`pointId`
HAVING
	`distance` < :distance AND `distance` > 0.0001
ORDER BY
	`distance`
LIMIT $this->count
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":distance" => $this->distance]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$points = parseItems($items, "\\Model\\Point");

			$list = new ListCount(sizeOf($points), $points);

			$distances = [];

			foreach ($items as $item) {
				$distances[] = [
					"pointId" => (int) $item["pointId"],
					"distance" => (double) $item["distance"]
				];
			}

			$list->putCustomData("distances", $distances);
			return $list;
		}
	}