<?php

	namespace Method\Photo;

	use IController;
	use APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $photoIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->photoIds = array_values(array_filter(explode(",", (string) $this->photoIds)));
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$photoIds = array_unique(array_map("intval", $this->photoIds));

			if (!sizeOf($photoIds)) {
				return [];
			}

			$sql = "SELECT * FROM `photo` WHERE `photoId` IN ('" . join("','", $photoIds) . "')";

			$data = $db->query($sql, DatabaseResultType::ITEMS);

			return parseItems($data, "\\Model\\Photo");
		}
	}