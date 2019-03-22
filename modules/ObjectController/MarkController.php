<?

	namespace ObjectController;

	use InvalidArgumentException;
	use Model\ListCount;
	use Model\Mark;
	use PDO;
	use RuntimeException;

	final class MarkController extends ObjectController
		implements IObjectControlGet, IObjectControlGetById, IObjectControlGetByIds, IObjectControlAdd, IObjectControlEdit, IObjectControlRemove {

		protected function getExpectedType() {
			return "\\Model\\Mark";
		}

		/**
		 * @param int $id
		 * @param int $count
		 * @param int $offset
		 * @param array|null $extra
		 * @return ListCount
		 */
		public function get($id, $count = 30, $offset = 0, $extra = null) {
			$sql = isset($extra["needCount"]) && $extra["needCount"]
				? "SELECT `mark`.*, COUNT(`pm`.`id`) AS `count` FROM `mark` LEFT JOIN `pointMark` `pm` ON `mark`.`markId` = `pm`.`markId` GROUP BY `mark`.`markId`"
				: "SELECT * FROM `mark`";

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), $this->getExpectedType());

			return new ListCount(sizeOf($items), $items);
		}

		/**
		 * @param int $id
		 * @param array|null $extra
		 * @return Mark
		 */
		public function getById($id, $extra = null) {
			$stmt = $this->mMainController->makeRequest("SELECT * FROM `mark` WHERE `markId` = ?");
			$stmt->execute([$id]);
			$item = $stmt->fetch(PDO::FETCH_ASSOC);
			return $item ? new Mark($item) : null;
		}

		/**
		 * @param int[]|string[]|string $ids
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getByIds($ids, $extra = null) {
			$ids = array_unique($ids);
			$sql = "SELECT * FROM `mark` WHERE `markId` IN (" . join(",", $ids) . ")";
			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return parseItems($items, $this->getExpectedType());
		}

		/**
		 * @param Mark $object
		 * @return Mark
		 */
		public function add($object) {
			if (!inRange($object->getColor(), 0x0, 0xffffff)) {
				throw new InvalidArgumentException("Invalid color");
			}

			$stmt = $this->mMainController->makeRequest("INSERT INTO `mark` (`title`, `color`) VALUES (:title, :color)");
			$stmt->execute([
				":title" => $object->getTitle(),
				":color" => $object->getColor()
			]);

			return $this->getById($this->mMainController->getDatabaseProvider()->lastInsertId());
		}

		/**
		 * @param Mark $object
		 * @return Mark
		 */
		public function edit($object) {
			if (!inRange($object->getColor(), 0x0, 0xffffff)) {
				throw new InvalidArgumentException("Invalid color code is specified");
			}

			if (mb_strlen($object->getTitle()) === 0) {
				throw new InvalidArgumentException("Title is empty");
			}

			$sql = "UPDATE `mark` SET `title` = :title, `color` = :color WHERE `markId` = :id";

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute([
				":id" => $object->getId(),
				":title" => $object->getTitle(),
				":color" => $object->getColor()
			]);

			if (!$stmt->rowCount()) {
				throw new RuntimeException("Not modified");
			}

			return $this->getById($object->getId());
		}

		/**
		 * @param Mark $object
		 * @return boolean
		 */
		public function remove($object) {
			if ($object === null) {
				throw new InvalidArgumentException("object is null");
			}

			$stmt = $this->mMainController->makeRequest("DELETE FROM `mark` WHERE `markId` = :id");
			$stmt->execute([":id" => $object->getId()]);

			return $stmt->rowCount();
		}

	}
