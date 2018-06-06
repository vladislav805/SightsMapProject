<?

	namespace Method\Point;

	use Method\APIPublicMethod;
	use Method\Mark\GetByPoints;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Point;
	use PDO;

	/**
	 * Поиск по всем местам
	 * query - ключевые слова
	 * count - количество выборки
	 * offset - сдвиг выборки
	 * order - сортировка результатов: 1/-1 - по дате создания; 2/-2 - по дате изменения; 3 - рейтингу. Отрицательные значения - обратная сортировка.
	 * @package Method\Point
	 */
	class Search extends APIPublicMethod {

		const ORDER_DATE_CREATE_ASC = 1;
		const ORDER_DATE_CREATE_DESC = -1;
		const ORDER_DATE_UPDATE_ASC = 2;
		const ORDER_DATE_UPDATE_DESC = -2;
		const ORDER_RATING = 3;

		/** @var string */
		protected $query;

		/** @var int */
		protected $cityId;

		/** @var int */
		protected $offset = 0;

		/** @var int */
		protected $count = 50;

		/** @var int */
		protected $order;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$sqlWhere = [];
			$sqlData = [];

			$words = mb_split(" ", $this->query);

			for ($i = 0, $l = sizeOf($words); $i < $l; ++$i) {
				$placeholder = ":pl" . $i;
				$sqlData[$placeholder] = "%" . $words[$i] . "%";
				$sqlWhere[] = "(`title` LIKE " . $placeholder . " OR `description` LIKE " . $placeholder . ")";
			}

			if ($this->cityId && is_numeric($this->cityId)) {
				$sqlWhere[] = "`point`.`cityId` = " . ((int) $this->cityId);
			}

			$sort = null;
			$order = null;
			$this->getOrderByConstruction($sort, $order);

			$whereClause = join(" AND ", $sqlWhere);

			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `point` WHERE " . $whereClause);
			$stmt->execute($sqlData);
			$count = (int) $stmt->fetch(PDO::FETCH_ASSOC)["count"];

			$orderAndLimit = sprintf(" ORDER BY `%s` %s LIMIT %d, %d", $sort, $order, $this->offset, $this->count);

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
		LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
WHERE 
	$whereClause
GROUP BY `point`.`pointId`
	$orderAndLimit
SQL;


			$stmt = $main->makeRequest($sql);

			$stmt->execute($sqlData);
			
			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Point");

			$pointIds = array_map(function(Point $item) {
				return $item->getId();
			}, $items);

			$marks = $main->perform(new GetByPoints((new Params)->set("pointIds", $pointIds)));

			array_walk($items, function(Point $item) use ($marks) {
				$id = $item->getId();
				if (isset($marks[$id])) {
					$item->setMarks($marks[$item->getId()]);
				}
				return $item;
			});

			/*$markList = [];

			foreach ($marks as $markId => $mark) {
				$markList[] = $mark;
			}*/

			$list = new ListCount((int) $count, $items);
			// $list->putCustomData("marks", $markList);

			return $list;
		}

		private function getOrderByConstruction(&$sort, &$order) {
			switch ((int) $this->order) {
				case self::ORDER_DATE_CREATE_ASC: $sort = "dateCreated"; $order = "ASC"; break;
				case self::ORDER_DATE_CREATE_DESC: $sort = "dateCreated"; $order = "DESC"; break;
				case self::ORDER_DATE_UPDATE_ASC: $sort = "dateUpdated"; $order = "ASC"; break;
				case self::ORDER_DATE_UPDATE_DESC: $sort = "dateUpdated"; $order = "DESC"; break;
				case self::ORDER_RATING:
				default:
					$sort = "rating"; $order = "DESC"; break;
			}
		}

	}