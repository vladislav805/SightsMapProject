<?

	namespace ObjectController;

	use PDO;

	final class UserController extends ObjectController
		implements IObjectControlGetById, IObjectControlGetByIds {

		protected function getExpectedType() {
			return "\\Model\\User";
		}

		/**
		 * @param int $id
		 * @param string[]|null $extra
		 * @return mixed
		 */
		public function getById($id, $extra = null) {
			$users = $this->getByIds($id, $extra);

			if (sizeOf($users) === 0) {
				return [];
			}

			return $users[0];
		}

		/**
		 * @param int[]|string[]|string $ids
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getByIds($ids, $extra = null) {
			$ids = $this->filterIds($ids);

			if (!sizeOf($ids) && $this->getCurrentUser()) {
				$ids = [$this->getCurrentUser()->getId()];
			}

			$userIds = array_unique(array_map(function($item) {
				return is_numeric($item) ? intval($item) : $item;
			}, $ids));

			list($eFields, $eJoin, $eCond) = $this->makeExtra($extra);

			$eFields = $eFields && sizeOf($eFields) ? ", " . join(", ", $eFields) : "";
			$eJoin = $eJoin && sizeOf($eJoin) ? join(" ", $eJoin) : "";
			$eCond = $eCond && sizeOf($eCond) ? " AND " . join(" AND ", $eCond) : "";

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

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute();

			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$extended = $extra !== null && is_array($extra) && in_array("extended", $extra) && sizeOf($items) === 1 && $this->getCurrentUser() && $this->getCurrentUser()->getId() == $items[0]["userId"];

			return parseItems($items, $extended ? "\\Model\\ExtendedUser" : "\\Model\\User");
		}

		private function filterIds($ids) {
			if (is_string($ids) || is_numeric($ids)) {
				$ids = array_values(
					array_filter(
						array_map("trim", explode(",", (string) $ids)), function($v) {
							return $v !== "";
						}
					)
				);
			} elseif (is_null($ids)) {
				$ids = [];
			} else {
				$ids = array_values($ids);
			}
			return $ids;
		}

		private function parseExtra($e) {
			return is_null($e) || is_string($e) && empty($e)
				? []
				: (is_array($e)
					? $e
					: explode(",", $e)
				);
		}

		/**
		 * @param string[] $extra
		 * @return string[][]
		 */
		private function makeExtra($extra) {
			$extra = $this->parseExtra($extra);

			if (!sizeOf($extra)) {
				return [[], [], []];
			}

			$fields = [];
			$join = [];
			$cond = [];

			foreach ($extra as $item) {
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