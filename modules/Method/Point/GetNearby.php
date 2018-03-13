<?

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->lat || !$this->lng) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if ($this->distance <= 0 || $this->distance > 2) {
				$this->distance = .5;
			}

			if ($this->count <= 0 || $this->count > 100) {
				$this->count = 20;
			}

			$sql = <<<REQ
SELECT
	*, (
		6371 * acos(
			cos(radians($this->lat)) * cos(radians(`lat`)) * cos(radians(`lng`) - radians($this->lng)) + sin(radians($this->lat)) * sin(radians(`lat`))
		)
    ) AS `distance`
FROM
	`point`
WHERE
	`lat` > $this->lat - 0.5
		AND
	`lat` < $this->lat + 0.5
        AND
    `lng` > $this->lng - 0.5
        AND
    `lng` < $this->lng + 0.5
HAVING
	`distance` < $this->distance AND `distance` != 0

ORDER BY
	`distance`
LIMIT $this->count;
REQ;

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$points = parseItems($items, "\\Model\\Point");

			$list = new ListCount(sizeOf($points), $points);

			$distances = [];

			foreach ($items as $item) {
				$distances[] = [
					"pointId" => $item["pointId"],
					"distance" => $item["distance"]
				];
			}

			$list->putCustomData("distances", $distances);
			return $list;
		}
	}