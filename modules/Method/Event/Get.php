<?php

	namespace Method\Event;

	use Method\APIPrivateMethod;
	use Model\Event;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	class Get extends APIPrivateMethod {

		/** @var string[] */
		protected $extra = "";

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
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
			$sightIds = [];
			$photoIds = [];

			foreach ($data as $event) {
				$userIds[] = $event->getOwnerUserId();
				$userIds[] = $event->getActionUserId();
				switch ($event->getType()) {
					case Event::EVENT_POINT_VERIFIED:
					case Event::EVENT_POINT_COMMENT_ADD:
					case Event::EVENT_POINT_ARCHIVED:
					case Event::EVENT_POINT_RATING_UP:
					case Event::EVENT_POINT_RATING_DOWN:
						$sightIds[] = $event->getSubjectId();
						break;

				}
			}

			$userIds = array_unique($userIds);
			$sightIds = array_unique($sightIds);
			$photoIds = array_unique($photoIds);

			$users = $main->perform(new \Method\User\GetByIds(["userIds" => $userIds, "extra" => $this->extra]));
			$points = $main->perform(new \Method\Sight\GetByIds(["sightIds" => join(",", $sightIds)]));
			$photos = $main->perform(new \Method\Photo\GetByIds(["photoIds" => join(",", $photoIds)]));

			$list->putCustomData("users", $users);
			$list->putCustomData("sights", $points);
			$list->putCustomData("photos", $photos);

			return $list;
		}
	}