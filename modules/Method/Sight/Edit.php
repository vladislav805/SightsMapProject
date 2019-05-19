<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Модификация информации о месте
	 * @package Method\Sight
	 */
	class Edit extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var string */
		protected $title;

		/** @var string */
		protected $description = "";

		/** @var int|null */
		protected $cityId = null;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId || !$this->title) {
				throw new APIException(ErrorCode::NO_PARAM, "sightId or title is not specified");
			}

			$setVerify = isTrustedUser($main->getUser()) ? "" : "`sight`.`isVerified` = 0,";
			$sql = <<<SQL
UPDATE
	`sight`, `user`, `authorize`
SET
	`sight`.`title` = :title,
	`sight`.`description` = :description,
	`sight`.`dateUpdated` = UNIX_TIMESTAMP(NOW()),
	{$setVerify}
	`sight`.`cityId` = :cityId
WHERE
	`sight`.`sightId` = :sightId AND
	(
        (`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
		`sight`.`ownerId` = `authorize`.`userId`
	) AND 
	`authorize`.`authKey` = :authKey
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":title" => $this->title,
				":description" => $this->description,
				":sightId" => $this->sightId,
				":cityId" => $this->cityId ? $this->cityId : null,
				":authKey" => $main->getAuthKey()
			]);

			//$userId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return $main->perform(new GetById(["sightId" => $this->sightId]));
		}
	}