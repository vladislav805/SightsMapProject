<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение родительного места
	 * @package Method\Point
	 */
	class SetParent extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var string */
		protected $parentId;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId) {
				throw new APIException(ErrorCode::NO_PARAM, "sightId is not specified");
			}

			$setVerify = isTrustedUser($main->getUser()) ? "" : "`point`.`isVerified` = 0";
			$sql = <<<SQL
UPDATE
	`point`, `user`, `authorize`
SET
	`point`.`parentId` = :pid,
	`point`.`dateUpdated` = UNIX_TIMESTAMP(NOW()),
	{$setVerify}
WHERE
	`point`.`pointId` = :sid AND
	(
        (`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
		`point`.`ownerId` = `authorize`.`userId`
	) AND 
	`authorize`.`authKey` = :authKey
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":pid" => $this->parentId > 0 ? $this->parentId : null,
				":sid" => $this->sightId,
				":authKey" => $main->getAuthKey()
			]);

			return $stmt->rowCount() > 0;
		}
	}