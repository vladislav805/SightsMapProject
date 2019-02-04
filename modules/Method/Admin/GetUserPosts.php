<?php

	namespace Method\Admin;

	use Method\APIModeratorMethod;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * @package Method\Admin
	 */
	class GetUserPosts extends APIModeratorMethod {

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {

			$sql = "SELECT `userId`, `firstName`, `lastName`, `status`, `login` FROM `user` WHERE `status` IN ('ADMIN', 'MODERATOR')";

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Moderator");

			return new ListCount(sizeOf($items), $items);
		}
	}