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

		/** @var boolean */
		protected $extended = false;

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
	`user`
		LEFT JOIN `city` ON `user`.`cityId` = `city`.`cityId`
		LEFT JOIN `photo` ON `user`.`userId` = `photo`.`ownerId` AND `photo`.`type` = 2 AND `photo`.`photoId` >= ALL (
			SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `user`.`userId` AND `photo`.`type` = 2
		)
WHERE
	(`user`.`userId` IN ('$userIds') OR `user`.`login` IN ('$userIds'))
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$this->extended = $this->extended && (sizeOf($items) === 1 && $main->isAuthorized() && $main->getSession()->getUserId() == $items[0]["userId"]);

			return parseItems($items, $this->extended ? "\\Model\\ExtendedUser" : "\\Model\\User");
		}
	}