<?php

	namespace Method\Point;

	use Model\IController;
	use Method\APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $pointIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->pointIds = array_values(array_filter(explode(",", (string) $this->pointIds)));
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$pointIds = array_unique(array_map("intval", $this->pointIds));

			if (!sizeOf($pointIds)) {
				return [];
			}

			$sql = "SELECT * FROM `point` WHERE `pointId` IN ('" . join("','", $pointIds) . "')";

			$data = $db->query($sql, DatabaseResultType::ITEMS);

			return parseItems($data, "\\Model\\Point");
		}
	}