<?

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * Поиск по всем местам
	 * query - ключевые слова
	 * count - количество выборки
	 * offset - сдвиг выборки
	 * order - сортировка результатов: 1/-1 - по дате создания; 2/-2 - по дате изменения; 3 - рейтингу. Отрицательные значения - обратная сортировка.
	 * @package Method\Sight
	 */
	class Search extends APIPublicMethod {

		const ORDER_DATE_CREATE_ASC = -1;
		const ORDER_DATE_CREATE_DESC = 1;
		const ORDER_DATE_UPDATE_ASC = -2;
		const ORDER_DATE_UPDATE_DESC = 2;
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

		/** @var boolean */
		protected $onlyWithPhotos = false;

		/** @var int */
		protected $offset = 0;

		/** @var int */
		protected $count = 50;

		/** @var int */
		protected $order;

		/**
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
				$sqlWhere[] = "(`title` LIKE " . $placeholder . " OR `sight`.`description` LIKE " . $placeholder . ")";
			}

			$sqlData[":uid"] = $main->isAuthorized() ? $main->getUser()->getId() : 0;

			$needPhotos = false;

			if ($this->cityId && is_numeric($this->cityId)) {
				$sqlWhere[] = "`sight`.`cityId` = " . ((int) $this->cityId);
			}

			if ((boolean) ((int) $this->isVerified)) {
				$sqlWhere[] = "`sight`.`isVerified` = 1";
			}

			if ((boolean) ((int) $this->isArchived)) {
				$sqlWhere[] = "`sight`.`isArchived` = 1";
			}

			if ((boolean) ((int) $this->onlyWithPhotos)) {
				$sqlWhere[] = "`photo`.`photoId` IS NOT NULL";
				$needPhotos = true;
			}

			$requestedCount = toRange($this->count, 1, 50);

			$extraTables = [];

			if ($main->getSession() && inRange($this->visitState, 0, 2)) {
				$extraTables[] = "sightVisit";
				$sqlWhere[] = sprintf("`sight`.`sightId` = `sightVisit`.`sightId` AND `sightVisit`.`userId` = %d AND `sightVisit`.`state` = %d", $main->getSession()->getUserId(), $this->visitState);
			}

			if ($this->markIds) {
				$marks = $this->markIds;

				if (is_string($marks)) {
					$marks = explode(",", $this->markIds);
				}

				$marks = array_map("intval", $marks);

				$extraTables[] = "sightMark";
				$sqlWhere[] = sprintf("`sight`.`sightId` = `sightMark`.`sightId` AND `sightMark`.`markId` IN (%s)", join(",", $marks));
			}

			$sort = null;
			$order = null;
			$this->getOrderByConstruction($sort, $order);

			if (!sizeOf($sqlWhere)) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$whereClause = join(" AND ", $sqlWhere);

			$extraTables = sizeOf($extraTables) ? ", `" . join("`, `", $extraTables) . "`" : "";

			$extraCountPhotos = $needPhotos ? "LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId` LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`" : "";

			$sqlCount = "SELECT COUNT(*) AS `count` FROM `sight` $extraCountPhotos $extraTables WHERE " . $whereClause;

			$stmt = $main->makeRequest($sqlCount);
			$stmt->execute($sqlData);
			$count = (int) $stmt->fetch(PDO::FETCH_ASSOC)["count"];

			$orderAndLimit = sprintf(" ORDER BY `%s` %s LIMIT %d, %d", $sort, $order, $this->offset, $requestedCount);

			$sql = <<<SQL
SELECT
	`sight`.*,
	IFNULL(`sightVisit`.`state`, 0) AS `visitState`,
    GROUP_CONCAT(DISTINCT `p`.`markId`) AS `markIds`,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`type`,
	`photo`.`path`,
	`photo`.`photo200`,
	`photo`.`photoMax`
FROM
	`sight`
		LEFT JOIN `sightPhoto` ON `sightPhoto`.`sightId` = `sight`.`sightId`
		LEFT JOIN `photo` ON `sightPhoto`.`photoId` = `photo`.`photoId`
		LEFT JOIN `city` ON `city`.`cityId` = `sight`.`cityId`
		LEFT JOIN `sightMark` `p` ON `p`.`sightId` = `sight`.`sightId`
		LEFT JOIN `sightVisit` ON `sightVisit`.`sightId` = `sight`.`sightId` AND `sightVisit`.`userId` = :uid
	$extraTables
WHERE 
	$whereClause
GROUP BY `sight`.`sightId`
	$orderAndLimit
SQL;
			$stmt = $main->makeRequest($sql);
			$stmt->execute($sqlData);

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Sight");

			$list = new ListCount((int) $count, $items);

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