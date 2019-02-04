<?php

	namespace Method\Admin;

	use Method\APIModeratorMethod;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	/**
	 * @package Method\Admin
	 */
	class GetBanned extends APIModeratorMethod {

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {

			$sql = "SELECT * FROM `ban` LEFT JOIN `user` ON `user`.`userId` = `ban`.`userId`";

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\BannedUser");

			return new ListCount(sizeOf($items), $items);
		}
	}