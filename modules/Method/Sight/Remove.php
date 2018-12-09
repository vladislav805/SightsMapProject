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
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId is not specified");
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
		`point`.`pointId` = :pointId AND
		(
			(`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
			`point`.`ownerId` = `authorize`.`userId`
		) AND
    	`authorize`.`authKey` = :authKey
)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":pointId" => $this->pointId, ":authKey" => $main->getAuthKey()]);

			return (boolean) $stmt->rowCount();
		}
	}