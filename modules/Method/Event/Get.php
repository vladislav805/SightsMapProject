<?php

	namespace Method\Event;

	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Event;
	use PDO;
	use tools\DatabaseConnection;

	class Get extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return ListCount
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$userId = $main->getSession()->getUserId();

			$sql = <<<SQL
SELECT
	`event`.`eventId`, `event`.`date`, `event`.`isNew`, `event`.`type`, `event`.`subjectId`, `event`.`actionUserId`, `event`.`ownerUserId`
FROM
	`event`, `authorize`
WHERE
	`ownerUserId` = `authorize`.`userId` AND `authorize`.`authKey` = :authKey
ORDER BY
	`eventId` DESC
LIMIT 100
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":authKey" => $main->getAuthKey()]);

			/** @var Event[] $data */
			$data = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Event");
			$list = new ListCount(sizeOf($data), $data);

			$userIds = [$userId];
			$pointIds = [];
			$photoIds = [];

			foreach ($data as $event) {
				$userIds[] = $event->getOwnerUserId();
				$userIds[] = $event->getActionUserId();
				switch ($event->getType()) {
					case Event::EVENT_POINT_VERIFIED:
					case Event::EVENT_POINT_COMMENT_ADD:
					case Event::EVENT_POINT_REMOVED:
					case Event::EVENT_PHOTO_ADDED:
						$pointIds[] = $event->getSubjectId();
						break;

				}
			}

			$userIds = array_unique($userIds);
			$pointIds = array_unique($pointIds);
			$photoIds = array_unique($photoIds);

			$users = $main->perform(new \Method\User\GetByIds(["userIds" => join(",", $userIds)]));
			$points = $main->perform(new \Method\Point\GetByIds(["pointIds" => join(",", $pointIds)]));
			$photos = $main->perform(new \Method\Photo\GetByIds(["photoIds" => join(",", $photoIds)]));

			$list->putCustomData("users", $users);
			$list->putCustomData("points", $points);
			$list->putCustomData("photos", $photos);

			return $list;
		}
	}