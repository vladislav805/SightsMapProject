<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetByIds extends APIPublicMethod {

		/** @var array */
		protected $userIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->userIds = array_values(array_filter(explode(",", (string) $this->userIds)));
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!sizeOf($this->userIds) && $main->getSession()) {
				$this->userIds = [$main->getSession()->getUserId()];
			}

			$userIds = array_unique(array_map(function($item) {
				return is_numeric($item) ? intval($item) : safeString($item);
			}, $this->userIds));

			$sql = sprintf("
SELECT
	*
FROM
	`user`, `photo` `p`
WHERE
	(`user`.`userId` IN ('%s') OR `user`.`login` IN ('%1\$s')) AND
	`user`.`userId` = `p`.`ownerId` AND
	`p`.`type` = 2 AND
	`p`.`photoId` >= ALL (
		SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `user`.`userId` AND `photo`.`type` = 2
	)", join("','", $userIds));

			$data = $db->query($sql, DatabaseResultType::ITEMS);

			return parseItems($data, "\\Model\\User");
		}
	}