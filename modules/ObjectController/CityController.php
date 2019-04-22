<?

	namespace ObjectController;

	use InvalidArgumentException;
	use Model\City;
	use Model\ListCount;
	use PDO;

	final class CityController extends ObjectController
		implements IObjectControlGet, IObjectControlGetByIds, IObjectControlAdd, IObjectControlRemove {

		protected function getExpectedType() {
			return "\\Model\\City";
		}

		/**
		 * @param int $id
		 * @param int $count
		 * @param int $offset
		 * @param array|null $extra
		 * @return mixed
		 */
		public function get($id, $count = 30, $offset = 0, $extra = null) {
			$stmt = $this->mMainController->makeRequest("SELECT * FROM `city`");
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), $this->getExpectedType());

			return new ListCount(sizeOf($items), $items);
		}

		/**
		 * @param int[]|string[]|string $ids
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getByIds($ids, $extra = null) {
			$ids = array_unique($ids);
			$sql = "SELECT * FROM `city` WHERE `cityId` IN (" . join(",", $ids) . ")";
			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute();

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), $this->getExpectedType());
		}

		/**
		 * @param City $object
		 * @return City
		 */
		public function add($object) {
			$stmt = $this->mMainController->makeRequest("INSERT INTO `city` (`name`, `parentId`, `lat`, `lng`, `radius`, `description`) VALUES (:title, :pid, :lat, :lng, :radius, :desc)");
			$stmt->execute([
				":title" => $object->getName(),
				":pid" => $object->getParentId(),
				":lat" => $object->getLat(),
				":lng" => $object->getLng(),
				":radius" => $object->getRadius(),
				":desc" => $object->getDescription()
			]);

			$cityId = $this->mMainController->getDatabaseProvider()->lastInsertId();

			list($city) = $this->getByIds([$cityId]);

			return $city;
		}

		/**
		 * @param City $object
		 * @return boolean
		 */
		public function remove($object) {
			if ($object === null) {
				throw new InvalidArgumentException("object is null");
			}

			$stmt = $this->mMainController->makeRequest("DELETE FROM `city` WHERE `cityId` = :id");
			$stmt->execute([":id" => $object->getId()]);

			return $stmt->rowCount() > 0;
		}

	}