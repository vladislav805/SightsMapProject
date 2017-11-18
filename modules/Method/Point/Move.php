<?php

	namespace Method\Point;

	use Method\APIPrivateMethod;
	use Method\APIException;
	use Model\IController;
	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Move extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Point
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId || !$this->lat || !$this->lng) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if (!isCoordinate($this->lat) || !isCoordinate($this->lng)) {
				throw new APIException(ERROR_INVALID_COORDINATES);
			}

			$ownerId = $main->getSession()->getUserId();

			$sql = sprintf("UPDATE `point` SET `lat` = '%f', `lng` = '%f', `isVerified` = 0 WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $this->lat, $this->lng, $ownerId, $this->pointId);

			$success = $db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			$ownerId > ADMIN_ID_LIMIT && $success && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return $main->perform(new GetById(["pointId" => $this->pointId]));
		}
	}