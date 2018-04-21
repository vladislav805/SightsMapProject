<?php

	namespace Method\Photo;

	use Model\IController;
	use Method\APIPublicMethod;
	use Model\Photo;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $photoIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->photoIds = array_unique( // get unique
				array_map("intval", // get int's
					array_values( // get actual values (reindex)
						array_filter( // remove empty
							explode(",", (string) $this->photoIds)
						)
					)
				)
			);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Photo[]
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$photoIds = $this->photoIds;

			if (!sizeOf($photoIds)) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT * FROM `photo` WHERE `photoId` IN (" . join(",", $photoIds) . ")");
			$stmt->execute();

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");
		}
	}