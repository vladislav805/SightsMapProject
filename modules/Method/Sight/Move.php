<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение местоположения места
	 * @package Method\Point
	 */
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
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId || !$this->lat || !$this->lng) {
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId or lat or lng is not specified");
			}

			if (!isCoordinate($this->lat, $this->lng)) {
				throw new APIException(ErrorCode::INVALID_COORDINATES, null, "Invalid coordinates");
			}

			$sql = <<<SQL
UPDATE
	`point`, `user`, `authorize`
SET
	`point`.`lat` = :lat,
	`point`.`lng` = :lng,
	`point`.`isVerified` = 0
WHERE
	`point`.`pointId` = :pointId AND
	(
        (`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
		`point`.`ownerId` = `authorize`.`userId`
	) AND 
	`authorize`.`authKey` = :authKey
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":lat" => $this->lat,
				":lng" => $this->lng,
				":pointId" => $this->pointId,
				":authKey" => $main->getAuthKey()
			]);

			$success = $stmt->rowCount();
			//$userId > ADMIN_ID_LIMIT && $success && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return (boolean) $success;
		}
	}