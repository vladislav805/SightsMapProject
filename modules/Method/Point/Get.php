<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Params;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Get extends APIPublicMethod {

		const MAX_LIMIT = 500;

		/** @var double */
		protected $lat1;

		/** @var double */
		protected $lng1;

		/** @var double */
		protected $lat2;

		/** @var double */
		protected $lng2;

		/** @var int */
		protected $markId;

		/** @var boolean */
		protected $onlyVerified;

		/** @var int */
		protected $ownerId = 0;

		/** @var int */
		protected $count = 500;

		/** @var int */
		protected $offset = 0;

		public function __construct($r) {
			parent::__construct($r);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return ListCount
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!($this->lat1 && $this->lat2 && $this->lng1 && $this->lng2) && !($this->ownerId)) {
				throw new APIException(ERROR_NO_PARAM, $_REQUEST);
			}

			$lat1 = min($this->lat1, $this->lat2);
			$lat2 = max($this->lat1, $this->lat2);
			$lng1 = min($this->lng1, $this->lng2);
			$lng2 = max($this->lng1, $this->lng2);

			$this->count = min($this->count, self::MAX_LIMIT);
			$this->offset = min(0, $this->offset);
var_dump($this->count, $this->offset);
			$this->lat1 = $lat1;
			$this->lat2 = $lat2;
			$this->lng1 = $lng1;
			$this->lng2 = $lng2;

			$list = $this->getPointsInArea($db);

			$pointIds = [];

			$userIds = array_unique(array_map(function(Point $placemark) use (&$pointIds) {
				$pointIds[] = $placemark->getId();
				return $placemark->getOwnerId();
			}, $list->getItems()));

			$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));
			$marks = $main->perform(new GetMarks((new Params())->set("pointIds", $pointIds)));
			$visited = $main->perform(new GetVisited(new Params));

			$user = $main->getUser();

			$items = $list->getItems();
			array_walk($items, function(Point $placemark) use ($user, $marks, $visited) {
				$user && $placemark->setAccessByCurrentUser($user);
				if (isset($marks[$placemark->getId()])) {
					$placemark->setMarks($marks[$placemark->getId()]);
				}
				$placemark->setVisitState(isset($visited[$placemark->getId()]) ? $visited[$placemark->getId()] : 0);
				return $placemark;
			});

			$list->putCustomData("users", $users);

			return $list;
		}

		/**
		 * @param DatabaseConnection $db
		 * @return ListCount
		 * @throws APIException
		 */
		public function getPointsInArea($db) {
			if ($this->onlyVerified) {
				$condition[] = "`isVerified` = '1'";
			}

			if ($this->ownerId) {
				$condition[] = "`ownerId` = '" . ((int) $this->ownerId) . "'";
			} else {
				$condition = [
					"'%1\$f' < `lat`",
					"`lat` < '%2\$f'",
					"'%3\$f' < `lng`",
					"`lng` < '%4\$f'"
				];
			}

			$sql = sprintf("SELECT * FROM `point` WHERE " . join(" AND ", $condition) . " ORDER BY `pointId` DESC LIMIT " . $this->offset . ",". $this->count, $this->lat1, $this->lat2, $this->lng1, $this->lng2);

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$items = parseItems($items, "\\Model\\Point");

			if ($this->ownerId) {
				$sql = sprintf("SELECT COUNT(*) FROM `point` WHERE " . join(" AND ", $condition));
				$count = $db->query($sql, DatabaseResultType::COUNT);
			} else {
				$count = sizeOf($items);
			}

			return new ListCount($count, $items);
		}

		/**
		 * @param DatabaseConnection $db
		 * @param int $ownerId
		 * @param int $count
		 * @param int $offset
		 * @param int $markId
		 * @return ListCount
		 * @throws APIException
		 * @deprecated
		 */
		public function getPointsList($db, $ownerId, $count, $offset = 0, $markId = 0) {
			$condition = [
				sprintf("`ownerId` = '%d'", $ownerId)
			];

			if ($markId) {
				$condition[] = sprintf("`markId` = '%d'", $markId);
			}

			$sql = sprintf("SELECT COUNT(*) FROM `point` WHERE " . join(" AND ", $condition));
			$countResult = $db->query($sql, DatabaseResultType::COUNT);

			$sql = sprintf("SELECT * FROM `point` WHERE " . join(" AND ", $condition) . " ORDER BY `pointId` DESC LIMIT " . $offset . "," . $count);

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$items = parseItems($items, "\\Model\\Point");

			return new ListCount($countResult, $items);
		}

	}