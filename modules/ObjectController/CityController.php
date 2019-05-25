<?

	namespace ObjectController;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\ErrorCode;
	use Model\City;
	use Model\ListCount;
	use PDO;
	use RuntimeException;

	final class CityController extends ObjectController
		implements IObjectControlGet, IObjectControlGetByIds, IObjectControlAdd, IObjectControlEdit, IObjectControlRemove {

		const EXTRA_WITH_COORDINATES = "withCoordinates";

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

			$cls = in_array(self::EXTRA_WITH_COORDINATES, $extra)
					? "\\Model\\StandaloneCity"
					: $this->getExpectedType();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), $cls);

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

			$cls = in_array(self::EXTRA_WITH_COORDINATES, $extra)
				? "\\Model\\StandaloneCity"
				: $this->getExpectedType();

			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), $cls);
		}

		/**
		 * @param City $object
		 * @return City
		 */
		public function add($object) {
			$stmt = $this->mMainController->makeRequest("INSERT INTO `city` (`name`, `parentId`, `lat`, `lng`, `radius`, `description`) VALUES (:title, :pid, :lat, :lng, :radius, :desc)");
			$stmt->execute([
				":title" => $object->getName(),
				":pid" => $object->getParentId() > 0 ? $object->getParentId() : null,
				":lat" => $object->getLat(),
				":lng" => $object->getLng(),
				":radius" => $object->getRadius(),
				":desc" => $object->getDescription()
			]);

			if ((int) $stmt->errorCode()) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, null, "error add city " . join(";", $stmt->errorInfo()));
			}

			$cityId = $this->mMainController->getDatabaseProvider()->lastInsertId();

			list($city) = $this->getByIds([$cityId]);

			return $city;
		}

		/**
		 * @param City $object
		 * @return City
		 */
		public function edit($object) {
			$sql = "UPDATE `city` SET `name` = :name, `parentId` = :pid, `lat` = :lat, `lng` = :lng, `radius` = :radius, `description` = :desc WHERE `cityId` = :id";

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute([
				":id" => $object->getId(),
				":name" => $object->getName(),
				":parentId" => $object->getParentId(),
				":lat" => $object->getLat(),
				":lng" => $object->getLng(),
				":radius" => $object->getRadius(),
				":description" => $object->getDescription()
			]);

			if (!$stmt->rowCount()) {
				throw new RuntimeException("Not modified");
			}

			return $this->getByIds([$object->getId()])[0];
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