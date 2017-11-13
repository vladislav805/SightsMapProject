<?php

	namespace Method\Point;

	use Model\IController;
	use Method\APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetMarks extends APIPublicMethod {

		/** @var int[] */
		protected $pointIds;

		/**
		 * GetMarks constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return array[]
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!sizeOf($this->pointIds)) {
				return [];
			}

			$pointIds = join(",", $this->pointIds);

			$sql = "SELECT `pointId`, `markId` FROM `pointMark` WHERE `pointId` IN (" . $pointIds . ")";
			$res = $db->query($sql, DatabaseResultType::ITEMS);
			$marks = [];

			foreach ($res as $row) {
				$marks[$row["pointId"]][] = $row["markId"];
			}

			return $marks;
		}
	}