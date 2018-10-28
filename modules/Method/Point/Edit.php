<?php

	namespace Method\Point;

	use Method\APIPrivateMethod;
	use Method\APIException;
	use Method\ErrorCode;
	use Model\IController;
	use function Method\Event\sendEvent;
	use Model\Event;

	/**
	 * Модификация информации о месте
	 * @package Method\Point
	 */
	class Edit extends APIPrivateMethod {

		protected $pointId;
		protected $title;
		protected $description = "";
		protected $cityId = null;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId || !$this->title) {
				throw new APIException(ErrorCode::NO_PARAM, "pointId or title is not specified");
			}

			$userId = $main->getSession()->getUserId();

			$sql = <<<SQL
UPDATE
	`point`, `authorize`
SET
	`point`.`title` = :title,
	`point`.`description` = :description,
	`point`.`dateUpdated` = UNIX_TIMESTAMP(NOW()),
	`point`.`isVerified` = 0,
	`point`.`cityId` = :cityId
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
				":cityId" => $this->cityId ? $this->cityId : null,
				":authKey" => $main->getAuthKey()
			]);

			$userId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return $main->perform(new GetById(["pointId" => $this->pointId]));
		}
	}