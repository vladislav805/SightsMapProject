<?

	namespace ObjectController;

	use Method\APIException;
	use Method\ErrorCode;
	use Model\City;
	use Model\Comment;
	use Model\ListCount;
	use Model\Sight;
	use Model\User;
	use PDO;
	use TypeError;

	final class CommentController extends ObjectController
		implements IObjectControlGet, IObjectControlGetById, IObjectControlAdd, IObjectControlRemove {

		protected function getExpectedType() {
			return "\\Model\\Comment";
		}

		/**
		 * @param int $sightId
		 * @param int $count
		 * @param int $offset
		 * @param array|null $extra
		 * @return ListCount
		 */
		public function get($sightId, $count = 30, $offset = 0, $extra = null) {
			$reqCount = toRange($count, 1, 100);
			$offset = max((int) $offset, 0);

			$stmt = $this->mMainController->makeRequest("SELECT COUNT(*) AS `count` FROM `comment` WHERE `sightId` = ?");
			$stmt->execute([$sightId]);
			$count = (int) $stmt->fetch(PDO::FETCH_ASSOC)["count"];

			if (!$count) {
				return new ListCount(0, []);
			}

			$sql = <<<SQL
SELECT
	DISTINCT `c`.`commentId`,
    `c`.`date` AS `date`,
    `c`.`text`,
	`u`.`userId`,
    `u`.`login`,
    `u`.`firstName`,
    `u`.`lastName`,
    `u`.`sex`,
    `u`.`lastSeen`,
    `h`.`photoId`,
    `h`.`type`,
    `h`.`date` AS `photoDate`,
    `h`.`path`,
    `h`.`photo200`,
    `h`.`photoMax`,
    `h`.`latitude`,
    `h`.`longitude`
FROM
	`comment` `c`,
	`user` `u` LEFT JOIN `photo` `h` on `u`.`photoId` = `h`.`photoId`
WHERE
	`c`.`sightId` = :sightId AND `c`.`userId` = `u`.`userId`
ORDER BY
	`commentId`
LIMIT $offset, $reqCount
SQL;

			$stmt = $this->mMainController->makeRequest($sql);
			$stmt->execute([":sightId" => $sightId]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/** @var Sight $sight */
			$sight = $this->mMainController->perform(new \Method\Sight\GetById(["sightId" => $sightId]));

			$ownerId = $sight->getOwnerId();

			/** @var Comment[] $comments */
			$comments = parseItems($items, $this->getExpectedType());

			/** @var User[] $users */
			$users = parseItems($items, "\\Model\\User");

			$currentUserId = $this->mMainController->getSession() ? $this->mMainController->getSession()->getUserId() : 0;
			$isOwner = $currentUserId === $ownerId;
			foreach($comments as $comment)  {
				$comment->setCanEdit($isOwner || $comment->getUserId() === $currentUserId);
			}

			return (new ListCount($count, $comments))->putCustomData("users", $users);
		}

		/**
		 * @param int $id
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getById($id, $extra = null) {
			$sql = $this->mMainController->makeRequest("SELECT * FROM `comment` WHERE `commentId` = ?");
			$sql->execute([$id]);
			$item = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ErrorCode::COMMENT_NOT_FOUND, null, "Comment with specified id not found");
			}

			return new Comment($item);
		}

		/**
		 * @param Comment $object
		 * @return Comment
		 */
		public function add($object) {
			$stmt = $this->mMainController->makeRequest("INSERT INTO `comment` (`sightId`, `date`, `userId`, `text`) VALUES (:sid, UNIX_TIMESTAMP(NOW()), :uid, :txt)");
			$stmt->execute([
				":sid" => $object->getSightId(),
				":uid" => $object->getUserId(),
				":txt" => $object->getText()
			]);

			$commentId = $this->mMainController->getDatabaseProvider()->lastInsertId();

			return $this->getById($commentId);
		}

		/**
		 * @param City $object
		 * @return boolean
		 */
		public function remove($object) {
			if ($object === null) {
				throw new APIException(ErrorCode::COMMENT_NOT_FOUND, null, "Comment not found");
			}

			if (!($object instanceof Comment)) {
				throw new TypeError("Passed object is not instance of Comment");
			}

			$currentUser = $this->mMainController->getUser();

			if ($object->getUserId() !== $currentUser->getId() && !isTrustedUser($currentUser)) {
				throw new APIException(ErrorCode::ACCESS_DENIED, null, "You cannot remove comment of another user");
			}

			$stmt = $this->mMainController->makeRequest("DELETE FROM `comment` WHERE `commentId` = :commentId AND `userId` = :userId");
			$stmt->execute([
				":commentId" => $object->getId(),
				":userId" => $object->getUserId()
			]);

			if (!$stmt->rowCount()) {
				throw new APIException(ErrorCode::COMMENT_NOT_FOUND, null, "Comment with specified commentId not found");
			}

			return $stmt->rowCount() > 0;
		}

	}