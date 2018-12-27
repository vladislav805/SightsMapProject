<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Удаление места с карты
	 * @package Method\Point
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
	`point`
WHERE `pointId` IN (
	SELECT
		`pointId`
	FROM
		`user`, `authorize`
	WHERE
		`point`.`pointId` = :sightId AND
		(
			(`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
			`point`.`ownerId` = `authorize`.`userId`
		) AND
    	`authorize`.`authKey` = :authKey
)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":sightId" => $this->sightId, ":authKey" => $main->getAuthKey()]);

			return (boolean) $stmt->rowCount();
		}
	}