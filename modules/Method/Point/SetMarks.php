<?php

	namespace Method\Point;

	use APIException;
	use APIPrivateMethod;
	use Model\Point;
	use Params;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class SetMarks extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int[] */
		protected $markIds;

		/**
		 * GetMarks constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
			$this->markIds = array_map("intval", explode(",", $this->markIds));
		}

		/**
		 * @param \IController $main
		 * @param DatabaseConnection $db
		 * @return Point
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			assertOwner($main, $point->getOwnerId(), ERROR_ACCESS_DENIED);

			$sql = sprintf("DELETE FROM `pointMark` WHERE `pointId` = '%d'", $this->pointId);
			$db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			$markIds = [];

			if (sizeOf($this->markIds)) {
				$sql = "SELECT `markId` FROM `mark` WHERE `markId` IN (" . join(",", $this->markIds) . ")";
				$verify = $db->query($sql, DatabaseResultType::ITEMS);

				$ids = array_map("intval", array_column($verify, "markId"));
				$markIds = $ids;

				foreach ($ids as &$markId) {
					$markId = sprintf("('%d', '%d')", $this->pointId, $markId);
				}

				$ids = array_values(array_filter($ids));


				if (sizeOf($ids)) {
					$sql = "INSERT INTO `pointMark` (`pointId`, `markId`) VALUES " . join(",", $ids);
					$db->query($sql, DatabaseResultType::AFFECTED_ROWS);
				}

			}

			/** @var Point $point */
			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			$point->setMarks($markIds);

			return $point;
		}
	}