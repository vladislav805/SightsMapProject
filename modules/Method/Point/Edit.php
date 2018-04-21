<?php

	namespace Method\Point;

	use Method\APIPrivateMethod;
	use Method\APIException;
	use Model\IController;
	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\Params;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	/**
	 * Модификация информации о месте
	 * @package Method\Point
	 */
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

			$userId = $main->getSession()->getUserId();

			$sql = <<<SQL
UPDATE
	`point`, `authorize`
SET
	`point`.`title` = :title,
	`point`.`description` = :description,
	`point`.`dateUpdated` = UNIX_TIMESTAMP(NOW()),
	`point`.`isVerified` = 0
WHERE
	`point`.`pointId` = :pointId AND
	`point`.`ownerId` = `authorize`.`userId` AND 
	`authorize`.`authKey` = :authKey
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":title" => $this->title,
				":description" => $this->description,
				":pointId" => $this->pointId,
				":authKey" => $main->getAuthKey()
			]);

			$userId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return $main->perform(new GetById(["pointId" => $this->pointId]));
		}
	}