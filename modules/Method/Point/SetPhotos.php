<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\IController;
	use Model\Params;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class SetPhotos extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int[] */
		protected $photoIds;

		public function __construct($request) {
			parent::__construct($request);
			$this->photoIds = array_map("intval", explode(",", $this->photoIds));
		}

		/**
		 * Realization of some action
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			/** @var Point $point */
			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			assertOwner($main, $point->getOwnerId(), ERROR_ACCESS_DENIED);

			$sql = sprintf("DELETE FROM `pointPhoto` WHERE `pointId` = '%d'", $this->pointId);
			$db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			if (sizeOf($this->photoIds)) {
				$sql = "SELECT `photoId` FROM `photo` WHERE `photoId` IN (" . join(",", $this->photoIds) . ")";
				$verify = $db->query($sql, DatabaseResultType::ITEMS);

				$ids = array_map("intval", array_column($verify, "photoId"));

				foreach ($ids as &$photoId) {
					$photoId = sprintf("('%d', '%d')", $this->pointId, $photoId);
				}

				$ids = array_values(array_filter($ids));


				if (sizeOf($ids)) {
					$sql = "INSERT INTO `pointPhoto` (`pointId`, `photoId`) VALUES " . join(",", $ids);
					$db->query($sql, DatabaseResultType::AFFECTED_ROWS);
				}

				if ($point->getOwnerId() != $main->getSession()->getUserId()) {
					sendEvent($main, $point->getOwnerId(), Event::EVENT_PHOTO_ADDED, $point->getId());
				}

			}

			return $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));
		}
	}