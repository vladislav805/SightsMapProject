<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Удаление места с карты
	 * @package Method\Sight
	 */
	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId is not specified");
			}

			$sql = <<<SQL
DELETE FROM
	`sight`
WHERE `sightId` IN (
	SELECT
		`sightId`
	FROM
		`user`, `authorize`
	WHERE
		`sight`.`sightId` = :sightId AND
		(
			(`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
			`sight`.`ownerId` = `authorize`.`userId`
		) AND
    	`authorize`.`authKey` = :authKey
)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":sightId" => $this->sightId, ":authKey" => $main->getAuthKey()]);

			return (boolean) $stmt->rowCount();
		}
	}