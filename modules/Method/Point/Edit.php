<?php

	namespace Method\Point;

	use APIPrivateMethod;
	use Method\APIException;
	use Model\IController;
	use function Method\Event\sendEvent;
	use Model\Event;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Edit extends APIPrivateMethod {

		protected $pointId;
		protected $title;
		protected $description = "";

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController	  $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId || !$this->title) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$ownerId = $main->getSession()->getUserId();

			$sql = sprintf("UPDATE `point` SET `title` = '%s', `description` = '%s', `dateUpdated` = UNIX_TIMESTAMP(NOW()), `isVerified` = '0' WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $this->title, $this->description, $ownerId, $this->pointId);

			$db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return $main->perform(new GetById(["pointId" => $this->pointId]));
		}
	}