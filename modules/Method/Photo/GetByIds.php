<?php

	namespace Method\Photo;

	use Model\IController;
	use Method\APIPublicMethod;
	use Model\Photo;
	use PDO;

	class GetByIds extends APIPublicMethod {

		/** @var int[] */
		protected $photoIds;

		public function __construct($request) {
			parent::__construct($request);
			if (!is_array($this->photoIds)) {
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
		}

		/**
		 * @param IController $main
		 * @return Photo[]
		 */
		public function resolve(IController $main) {
			$photoIds = $this->photoIds;

			if (!sizeOf($photoIds)) {
				return [];
			}

			$stmt = $main->makeRequest("SELECT * FROM `photo` WHERE `photoId` IN (" . join(",", $photoIds) . ")");
			$stmt->execute();

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");
		}
	}