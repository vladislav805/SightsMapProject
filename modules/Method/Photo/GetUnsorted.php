<?php

	namespace Method\Photo;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Photo;
	use PDO;

	class GetUnsorted extends APIPublicMethod {

		/** @var int */
		protected $count = 30;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return Photo[]
		 */
		public function resolve(IController $main) {
			$this->count = toRange($this->count, 1,  50);

			$stmt = $main->makeRequest("SELECT `p`.* FROM `photo` `p` WHERE `p`.`type` != :not_type AND NOT EXISTS ( SELECT `s`.`photoId` FROM `sightPhoto` `s` WHERE `s`.`photoId` = `p`.`photoId` ) LIMIT {$this->offset}, {$this->count}");
			$stmt->execute([":not_type" => Photo::TYPE_PROFILE]);

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");
		}
	}