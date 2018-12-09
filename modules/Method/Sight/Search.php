<?

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Method\Mark\GetByPoints;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Sight;
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

		/** @var int[] */
		protected $markIds;

		/** @var int */
		protected $visitState = -1;

		/** @var boolean */
		protected $isVerified;

		/** @var boolean */
		protected $isArchived;

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
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sqlWhere = [];
			$sqlData = [];

			$words = mb_split(" ", $this->query);

			for ($i = 0, $l = sizeOf($words); $i < $l; ++$i) {
				if (empty($words[$i])) {
					continue;
				}
				$placeholder = ":pl" . $i;
				$sqlData[$placeholder] = "%" . $words[$i] . "%";
				$sqlWhere[] = "(`title` LIKE " . $placeholder . " OR `description` LIKE " . $placeholder . ")";
			}

			if ($this->cityId && is_numeric($this->cityId)) {
				$sqlWhere[] = "`point`.`cityId` = " . ((int) $this->cityId);
			}

			if ($this->isVerified) {
				$sqlWhere[] = "`point`.`isVerified` = 1";
			}

			if ($this->isArchived) {
				$sqlWhere[] = "`point`.`isArchived` = 1";
			}

			$extraTables = [];

			if ($main->getSession() && inRange($this->visitState, 0, 2)) {
				$extraTables[] = "pointVisit";
				$sqlWhere[] = sprintf("`point`.`pointId` = `pointVisit`.`pointId` AND `pointVisit`.`userId` = %d AND `pointVisit`.`state` = %d", $main->getSession()->getUserId(), $this->visitState);
			}

			if ($this->markIds) {
				$marks = array_map("intval", explode(",", $this->markIds));

				$extraTables[] = "pointMark";
				$sqlWhere[] = sprintf("`point`.`pointId` = `pointMark`.`pointId` AND `pointMark`.`markId` IN (%s)", join(",", $marks));
			}

			$sort = null;
			$order = null;
			$this->getOrderByConstruction($sort, $order);

			if (!sizeOf($sqlWhere)) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$whereClause = join(" AND ", $sqlWhere);

			$extraTables = sizeOf($extraTables) ? ", `" . join("`, `", $extraTables) . "`" : "";

			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `point` $extraTables WHERE " . $whereClause);
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
	$extraTables
WHERE 
	$whereClause
GROUP BY `point`.`pointId`
	$orderAndLimit
SQL;

			$stmt = $main->makeRequest($sql);

			$stmt->execute($sqlData);
			
			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Sight");

			$pointIds = array_map(function(Sight $item) {
				return $item->getId();
			}, $items);

			$marks = $main->perform(new GetByPoints((new Params)->set("pointIds", $pointIds)));

			array_walk($items, function(Sight $item) use ($marks) {
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