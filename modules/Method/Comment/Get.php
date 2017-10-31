<?php

	namespace Method\Comment;

	use APIPublicMethod;
	use ListCount;
	use Model\Comment;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Get extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		/** @var int */
		protected $count = 50;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param \IController       $main
		 * @param DatabaseConnection $db
		 * @return ListCount
		 * @throws \APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {

			$sql = sprintf("SELECT * FROM `comment` WHERE `pointId` = '%d' LIMIT " . $this->offset . "," . $this->count, $this->pointId);
			$items = $db->query($sql, DatabaseResultType::ITEMS);

			$sql = sprintf("SELECT COUNT(*) FROM `comment` WHERE `pointId` = '%d'", $this->pointId);
			$count = $db->query($sql, DatabaseResultType::COUNT);

			$items = parseItems($items, "\\Model\\Comment");

			$list = new ListCount($count, $items);

			$userIds = array_unique(array_map(function(Comment $comment) {
				return $comment->getUserId();
			}, $items));

			$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));

			$list->putCustomData("users", $users);

			return $list;
		}
	}