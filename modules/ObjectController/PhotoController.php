<?

	namespace ObjectController;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\ErrorCode;
	use Model\ListCount;
	use Model\Photo;
	use PDO;

	final class PhotoController extends ObjectController
		implements IObjectControlGet, IObjectControlGetById, IObjectControlRemove {

		protected function getExpectedType() {
			return "\\Model\\Photo";
		}

		/**
		 * @param int $id
		 * @param int $count
		 * @param int $offset
		 * @param array|null $extra
		 * @return mixed
		 */
		public function get($id, $count = 30, $offset = 0, $extra = null) {
			$isSight = $extra !== null && array_key_exists("sight", $extra);

			if (!$id) {
				throw new APIException(ErrorCode::NO_PARAM, null, "ownerId and sightId is not specified");
			}

			$count = (int) $count;
			$offset = (int) $offset;

			$type = $isSight ? Photo::TYPE_SIGHT : Photo::TYPE_PROFILE;
			$params = [":id" => $id, ":type" => $type];

			if ($isSight) {
				$sql = <<<SQL
SELECT
	*
FROM
     `photo`,
     `sightPhoto`
WHERE
      `photo`.`type` IN (:type, :typeSuggest) AND
      `sightPhoto`.`sightId` = :id AND
      `photo`.`photoId` = `sightPhoto`.`photoId`
ORDER BY `photo`.`type`, `sightPhoto`.`id`
LIMIT {$offset}, {$count}
SQL;
				$params[":typeSuggest"] = Photo::TYPE_SIGHT_SUGGESTED;
			} else {
				$sql = <<<SQL
SELECT
	*
FROM
     `photo`
WHERE
      `type` = :type AND
      `ownerId` = :id
ORDER BY `photoId` DESC
LIMIT {$offset}, {$count}
SQL;
			}

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute($params);

			/** @var Photo[] $items */
			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");

			$res = new ListCount(sizeof($items), $items);

			if ($isSight) {
				$userIds = [];

				foreach ($items as $photo) {
					$userIds[] = $photo->getOwnerId();
				}

				$users = (new UserController($this->mMainController))->getByIds($userIds);

				$res->putCustomData("users", $users);
			}
			return $res;
		}

		/**
		 * @param int $id
		 * @param null $extra
		 * @return Photo
		 */
		public function getById($id, $extra = null) {
			if (!$id) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Not specified photoId");
			}

			$sql = $this->mMainController->makeRequest("SELECT * FROM `photo` WHERE `photoId` = ?");
			$sql->execute([$id]);
			$data = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$data) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND, null, "Photo not found");
			}

			return new Photo($data);
		}

		/**
		 * @param Photo $object
		 * @return boolean
		 */
		public function remove($object) {
			if ($object === null || !($object instanceof Photo)) {
				throw new InvalidArgumentException("object is null or not instance of Photo");
			}

			$stmt = $this->mMainController->makeRequest("DELETE FROM `photo` WHERE `photoId` = :photoId LIMIT 1");
			$stmt->execute([":photoId" => $object->getId()]);

			$success = $stmt->rowCount() > 0;

			if ($success) {
				unlink(ROOT_PROJECT . "/userdata/" . $object->getPath() . "/" . $object->getNameThumbnail());
				unlink(ROOT_PROJECT . "/userdata/" . $object->getPath() . "/" . $object->getNameOriginal());
			}

			return $success;
		}

	}