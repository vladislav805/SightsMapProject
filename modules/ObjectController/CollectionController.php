<?

	namespace ObjectController;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\ErrorCode;
	use Model\Collection;
	use Model\ListCount;
	use Model\User;
	use PDO;
	use RuntimeException;

	final class CollectionController extends ObjectController
		implements IObjectControlGet, IObjectControlGetById, IObjectControlAdd, IObjectControlEdit, IObjectControlRemove {

		protected function getExpectedType() {
			return "\\Model\\Collection";
		}

		/**
		 * @param int $id
		 * @param int $count
		 * @param int $offset
		 * @param array|null $extra
		 * @return mixed
		 */
		public function get($id, $count = 30, $offset = 0, $extra = null) {
			$condition = [];
			$whereData = [];

			if ($extra && array_key_exists("ownerId", $extra)) {
				$condition[] = "`collection`.`ownerId` = :oid";
				$whereData[":oid"] = $extra["ownerId"];
			}

			if ($extra && array_key_exists("cityId", $extra)) {
				$condition[] = "`collection`.`cityId` = :cid";
				$whereData[":cid"] = $extra["cityId"];
			}

			$condition = sizeof($condition) ? " WHERE " . join(" AND ", $condition) : "";

			$sql = <<<SQL
SELECT
	`collection`.*,
    `u`.`userId` AS `userUserId`,
    `u`.`login` AS `userLogin`,
    `u`.`status` AS `userStatus`,
	`u`.`firstName` AS `userFirstName`,
	`u`.`lastName` AS `userLastName`,
	`u`.`sex` AS `userSex`,
	`u`.`photoId` AS `userPhotoId`,
	`u`.`cityId` AS `userCityId`,
	`u`.`lastSeen` AS `userLastSeen`,
    `c`.`name` AS `cityName`,
    `c`.`name4child` as `cityName4child`,
    `uc`.`cityId` AS `userCityId`,
    `uc`.`name` AS `userName`,
    `uc`.`name4child` AS `userName4child`
FROM
     `collection`
         LEFT JOIN `user` `u` ON `collection`.`ownerId` = `u`.`userId`
         LEFT JOIN `city` `c` ON `collection`.`cityId` = `c`.`cityId`
         LEFT JOIN `city` `uc` ON `u`.`cityId` = `uc`.`cityId`
SQL;

			$sql .= $condition;

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute($whereData);

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$items = parseItems($data, $this->getExpectedType());
			$users = [];

			foreach ($data as $item) {
				if (array_key_exists("userFirstName", $item) && $item["userFirstName"] && !isset($users[$item["userUserId"]])) {
					$user = new User(get_object_of_prefix($item, "user"));
					$users[$user->getId()] = $user;
				}
			}

			return (new ListCount(sizeOf($items), $items))->putCustomData("users", array_values($users));
		}

		/**
		 * @param int $id
		 * @param array|null $extra
		 * @return Collection
		 */
		public function getById($id, $extra = null) {
			$sql = <<<SQL
SELECT
	`collection`.*,
    `u`.`userId` AS `userUserId`,
    `u`.`login` AS `userLogin`,
    `u`.`status` AS `userStatus`,
	`u`.`firstName` AS `userFirstName`,
	`u`.`lastName` AS `userLastName`,
	`u`.`sex` AS `userSex`,
	`u`.`photoId` AS `userPhotoId`,
	`u`.`cityId` AS `userCityId`,
	`u`.`lastSeen` AS `userLastSeen`,
    `c`.`name` AS `cityName`,
    `c`.`name4child` as `cityName4child`,
    `uc`.`cityId` AS `userCityId`,
    `uc`.`name` AS `userName`,
    `uc`.`name4child` AS `userName4child`
FROM
     `collection`
         LEFT JOIN `user` `u` ON `collection`.`ownerId` = `u`.`userId`
         LEFT JOIN `city` `c` ON `collection`.`cityId` = `c`.`cityId`
         LEFT JOIN `city` `uc` ON `u`.`cityId` = `uc`.`cityId`
WHERE
      `collectionId` = ?
SQL;
			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute([$id]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$result) {
				throw new APIException(ErrorCode::COLLECTION_NOT_FOUND);
			}

			$cls = $this->getExpectedType();
			return new $cls($result);
		}

		/**
		 * @param Collection $object
		 * @return Collection
		 */
		public function add($object) {
			$stmt = $this->mMainController->makeRequest("INSERT INTO `collection` (`ownerId`, `title`, `description`, `dateCreated`, `cityId`, `type`) VALUES (:oid, :title, :desc, :dateCreate, :cityId, :type)");
			$stmt->execute([
				":oid" => $object->getOwnerId(),
				":title" => $object->getTitle(),
				":desc" => $object->getDescription(),
				":dateCreate" => $object->getDateCreated(),
				":cityId" => $object->getCityId(),
				":type" => $object->getType(),
			]);

			if ((int) $stmt->errorCode()) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, null, "error add collection " . join(";", $stmt->errorInfo()));
			}

			$collectionId = $this->mMainController->getDatabaseProvider()->lastInsertId();

			return $this->getById($collectionId);
		}

		/**
		 * @param Collection $object
		 * @return Collection
		 */
		public function edit($object) {
			$sql = "UPDATE `collection` SET `title` = :title, `description` = :desc, `dateCreated` = :dc, `dateUpdated` = :du, `cityId` = :cityId, `type` = :type WHERE `collectionId` = :id";

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute([
				":id" => $object->getId(),
				":title" => $object->getTitle(),
				":desc" => $object->getDescription(),
				":dc" => $object->getDateCreated(),
				":du" => $object->getDateUpdated(),
				":cityId" => $object->getCityId(),
				":type" => $object->getType()
			]);

			if (!$stmt->rowCount()) {
				throw new RuntimeException("Not modified");
			}

			return $this->getById($object->getId());
		}

		/**
		 * @param Collection $object
		 * @return boolean
		 */
		public function remove($object) {
			if ($object === null) {
				throw new InvalidArgumentException("object is null");
			}

			if (!($object instanceof Collection)) {
				throw new InvalidArgumentException("object is not instance of Collection");
			}

			$stmt = $this->mMainController->makeRequest("DELETE FROM `collection` WHERE `collectionId` = :id");
			$stmt->execute([":id" => $object->getId()]);

			return $stmt->rowCount() > 0;
		}

	}