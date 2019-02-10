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

		/** @var string[] */
		protected $extra = [];

		public function __construct($request) {
			parent::__construct($request);

			if (is_string($this->userIds) || is_numeric($this->userIds)) {
				$this->userIds = array_values(
					array_filter(
						array_map("trim", explode(",", (string) $this->userIds)),
						function($v) {
							return $v !== "";
						}
					)
				);
			} else {
				$this->userIds = array_values($this->userIds);
			}

			$this->extra = is_array($this->extra) ? $this->extra : explode(",", $this->extra);
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

			list($eFields, $eJoin, $eCond) = $this->makeExtra();

			$eFields = sizeOf($eFields) ? ", " . join(", ", $eFields) : "";
			$eJoin = sizeOf($eJoin) ? join(" ", $eJoin) : "";
			$eCond = sizeOf($eCond) ? " AND " . join(" AND ", $eCond) : "";

			$ids = [];
			$usernames = [];

			foreach ($userIds as $item) {
				if (is_numeric($item)) {
					$ids[] = $item;
				} else {
					$usernames[] = $item;
				}
			}

			$ids = sizeOf($ids) ? join(",", $ids) : "NULL";
			$usernames = sizeOf($usernames) ? join("','", $usernames) : "NULL";

			$sql = <<<SQL
SELECT
	*{$eFields}
FROM
	`user` {$eJoin}
WHERE
	(`user`.`userId` IN ($ids) OR `user`.`login` IN ('$usernames')){$eCond}
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$this->extended = $this->extended && (sizeOf($items) === 1 && $main->isAuthorized() && $main->getSession()->getUserId() == $items[0]["userId"]);

			return parseItems($items, $this->extended ? "\\Model\\ExtendedUser" : "\\Model\\User");
		}

		private function makeExtra() {
			if (!sizeOf($this->extra)) {
				return "";
			}

			$fields = [];
			$join = [];
			$cond = [];

			foreach ($this->extra as $item) {
				switch ($item) {
					case "rating":
						$fields[] = "getUserRating(`user`.`userId`) AS `rating`";
						break;

					case "photo":
						$join[] = "LEFT JOIN `photo` ON `photo`.`photoId` = `user`.`photoId`";
						break;

					case "city":
						$join[] = "LEFT JOIN `city` ON `user`.`cityId` = `city`.`cityId`";
						break;
				}
			}

			return [$fields, $join, $cond];
		}
	}