<?php

	namespace Method\Photo;

	use Model\IController;
	use Method\APIPublicMethod;
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
			$stmt = $main->makeRequest("SELECT `p`.* FROM `photo` `p` WHERE `p`.`type` != :type AND NOT EXISTS ( SELECT `s`.`photoId` FROM `pointPhoto` `s` WHERE `s`.`photoId` = `p`.`photoId` )");
			$stmt->execute([":type" => Photo::TYPE_PROFILE]);

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");
		}
	}