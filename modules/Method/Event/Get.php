<?php

	namespace Method\Event;

	use Method\APIException;
	use APIPrivateMethod;
	use Model\IController;
	use Model\ListCount;
	use Model\Event;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Get extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$userId = $main->getSession()->getUserId();

			$sql = sprintf("SELECT * FROM `event` WHERE `ownerUserId` = '%d' ORDER BY `eventId` DESC LIMIT 100", $userId);

			/** @var Event[] $data */
			$data = parseItems($db->query($sql, DatabaseResultType::ITEMS), "\\Model\\Event");
			$list = new ListCount(sizeOf($data), $data);

			$userIds = [$userId];
			$pointIds = [];
			$photoIds = [];

			foreach ($data as $event) {
				$userIds[] = $event->getOwnerUserId();
				switch ($event->getType()) {
					case Event::EVENT_POINT_VERIFIED:
					case Event::EVENT_POINT_COMMENT_ADD:
					case Event::EVENT_POINT_COMMENT_REPORT:
					case Event::EVENT_POINT_REPORT:
					case Event::EVENT_POINT_REMOVED:
						$pointIds[] = $event->getSubjectId();
						break;

					case Event::EVENT_PHOTO_ACCEPTED:
						$photoIds[] = $event->getSubjectId();
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