<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Sight;

	/**
	 * Добавление нового места на карту
	 * @package Method\Sight
	 */
	class Add extends APIPrivateMethod {

		/** @var double */
		protected $lat;

		/** @var double */
		protected $lng;

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
			if (!$this->lat || !$this->lng || !$this->title) {
				throw new APIException(ErrorCode::NO_PARAM, null, "lat, lng or title is not specified");
			}

			if (!isCoordinate($this->lat, $this->lng)) {
				throw new APIException(ErrorCode::INVALID_COORDINATES, null, "Specified coordinates is invalid");
			}

			$userId = $main->getSession()->getUserId();

			$stmt = $main->makeRequest("INSERT INTO `sight` (`ownerId`, `lat`, `lng`, `dateCreated`, `title`, `description`, `cityId`) VALUES (?, ?, ?, UNIX_TIMESTAMP(NOW()), ?, ?, ?)");
			$stmt->execute([$userId, $this->lat, $this->lng, $this->title, $this->description, $this->cityId ? $this->cityId : null]);

			$sightId = $main->getDatabaseProvider()->lastInsertId();

			/** @var Sight $sight */
			$sight = $main->perform(new GetById(["sightId" => $sightId]));

			($user = $main->getUser()) && $sight->setAccessByCurrentUser($user);

			//$userId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $sightId);

			return $sight;
		}
	}