<?php

	namespace Method\Point;

	use APIException;
	use IController;
	use ListCount;
	use Model\Point;
	use APIPublicMethod;
	use Params;
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
			if (!($this->lat1 && $this->lat2 && $this->lng1 && $this->lng2)) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$list = $this->getPointsInArea($db);

			$pointIds = [];

			$userIds = array_unique(array_map(function(Point $placemark) use (&$pointIds) {
				$pointIds[] = $placemark->getId();
				return $placemark->getOwnerId();
			}, $list->getItems()));

			$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));
			$marks = $main->perform(new \Method\Point\GetMarks((new Params())->set("pointIds", $pointIds)));
			$visited = $main->perform(new \Method\Point\GetVisited(new Params));

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
			$condition = [
				"'%f' < `lat`",
				"`lat` < '%f'",
				"'%f' < `lng`",
				"`lng` < '%f'"
			];

			if ($this->onlyVerified) {
				$condition[] = "`isVerified` = '1'";
			}

			$sql = sprintf("SELECT * FROM `point` WHERE " . join(" AND ", $condition) . " ORDER BY `pointId` DESC LIMIT " . self::MAX_LIMIT, $this->lat1, $this->lat2, $this->lng1, $this->lng2);

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$items = parseItems($items, "\\Model\\Point");

			return new ListCount(sizeOf($items), $items);
		}

		/**
		 * @param DatabaseConnection $db
		 * @param int                 $ownerId
		 * @param int                 $count
		 * @param int                 $offset
		 * @param int                 $markId
		 * @return ListCount
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

			// TODO: markId
			// TODO: isPublic
			$sql = sprintf("SELECT * FROM `point` WHERE " . join(" AND ", $condition) . " ORDER BY `pointId` DESC LIMIT " . $offset . "," . $count);

			$items = $db->query($sql, DatabaseResultType::ITEMS);
			$items = parseItems($items, "\\Model\\Point");

			return new ListCount($countResult, $items);
		}

	}