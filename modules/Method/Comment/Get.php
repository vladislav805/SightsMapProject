<?php

	namespace Method\Comment;

	use Method\APIPublicMethod;
	use Model\Comment;
	use Model\IController;
	use Model\ListCount;
	use Model\User;
	use PDO;

	/**
	 * Получение комментариев к месту
	 * @package Method\Comment
	 */
	class Get extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $count = 50;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			$reqCount = max(1, min($this->count, 100));
			$offset = max((int) $this->offset, 0);

			$stmt = $main->makeRequest("SELECT COUNT(*) AS `count` FROM `comment` WHERE `pointId` = ?");
			$stmt->execute([$this->sightId]);
			$count = (int) $stmt->fetch(PDO::FETCH_ASSOC)["count"];

			if (!$count) {
				return new ListCount(0, []);
			}

			$sql = <<<SQL
SELECT
	DISTINCT `c`.`commentId`,
    `c`.`date`,
    `c`.`text`,
	`u`.`userId`,
    `u`.`login`,
    `u`.`firstName`,
    `u`.`lastName`,
    `u`.`sex`,
    `u`.`lastSeen`,
    `h`.`photoId`,
    `h`.`type`,
    `h`.`date`,
    `h`.`path`,
    `h`.`photo200`,
    `h`.`photoMax`,
    `h`.`latitude`,
    `h`.`longitude`
FROM
	`comment` `c`,
	`user` `u` LEFT JOIN `photo` `h` on `u`.`photoId` = `h`.`photoId`
WHERE
	`c`.`pointId` = :pointId AND `c`.`userId` = `u`.`userId`
ORDER BY
	`commentId` ASC
LIMIT $offset, $reqCount
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":pointId" => $this->sightId]);
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/** @var Comment[] $comments */
			$comments = parseItems($items, "\\Model\\Comment");

			/** @var User[] $users */
			$users = parseItems($items, "\\Model\\User");

			$currentUserId = $main->getSession() ? $main->getSession()->getUserId() : 0;
			foreach($comments as $comment)  {
				$comment->setCurrentUser($currentUserId);
			}

			return (new ListCount($count, $comments))->putCustomData("users", $users);
		}
	}