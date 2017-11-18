<?php

	namespace Method\Point;

	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\IController;
	use Method\APIPrivateMethod;
	use Method\APIException;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Add extends APIPrivateMethod {

		protected $lat;
		protected $lng;
		protected $title;
		protected $description = "";

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
			if (!$this->lat || !$this->lng || !$this->title) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if (!isCoordinate($this->lat) || !isCoordinate($this->lng)) {
				throw new APIException(ERROR_INVALID_COORDINATES);
			}

			$ownerId = $main->getSession()->getUserId();

			$sql = sprintf("INSERT INTO `point` (`ownerId`, `lat`, `lng`, `dateCreated`, `title`, `description`) VALUES ('%d', '%f', '%f', UNIX_TIMESTAMP(NOW()), '%s', '%s')", $ownerId, $this->lat, $this->lng, $this->title, $this->description);

			$pointId = $main->query($sql, DatabaseResultType::INSERTED_ID);

			$point = $main->perform(new GetById(["pointId" => $pointId]));

			($user = $main->getUser()) && $point->setAccessByCurrentUser($user);

			$ownerId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $pointId);

			return $point;
		}
	}