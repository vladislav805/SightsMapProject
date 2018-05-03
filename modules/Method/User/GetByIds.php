<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\User;
	use PDO;

	/**
	 * Получение информации о пользователях из БД по их идентификаторам
	 * @package Method\User
	 */
	class GetByIds extends APIPublicMethod {

		/** @var int[]|string[] */
		protected $userIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->userIds = array_values(array_filter(explode(",", (string) $this->userIds)));
		}

		/**
		 * @param IController $main
		 * @return User[]
		 */
		public function resolve(IController $main) {
			if (!sizeOf($this->userIds) && $main->getSession()) {
				$this->userIds = [$main->getSession()->getUserId()];
			}

			$userIds = array_unique(array_map(function($item) {
				return is_numeric($item) ? intval($item) : $item;
			}, $this->userIds));

			$userIds = join("','", $userIds);
			$sql = <<<SQL
SELECT
	*
FROM
	`user`, `photo` `p`
WHERE
	(`user`.`userId` IN ('$userIds') OR `user`.`login` IN ('$userIds')) AND
	`user`.`userId` = `p`.`ownerId` AND
	`p`.`type` = 2 AND
	`p`.`photoId` >= ALL (
		SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `user`.`userId` AND `photo`.`type` = 2
	)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\User");
		}
	}