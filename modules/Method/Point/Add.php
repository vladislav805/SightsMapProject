<?php

	namespace Method\Point;

	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\IController;
	use Method\APIPrivateMethod;
	use Method\APIException;
	use Model\Point;

	/**
	 * Добавление нового места на карту
	 * @package Method\Point
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

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->lat || !$this->lng || !$this->title) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if (!isCoordinate($this->lat) || !isCoordinate($this->lng)) {
				throw new APIException(ERROR_INVALID_COORDINATES);
			}

			$userId = $main->getSession()->getUserId();

			$stmt = $main->makeRequest("INSERT INTO `point` (`ownerId`, `lat`, `lng`, `dateCreated`, `title`, `description`, `cityId`) VALUES (?, ?, ?, UNIX_TIMESTAMP(NOW()), ?, ?, ?)");
			$stmt->execute([$userId, $this->lat, $this->lng, $this->title, $this->description, $this->cityId ? $this->cityId : null]);

			$pointId = $main->getDatabaseProvider()->lastInsertId();

			/** @var Point $point */
			$point = $main->perform(new GetById(["pointId" => $pointId]));

			($user = $main->getUser()) && $point->setAccessByCurrentUser($user);

			$userId > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $pointId);

			return $point;
		}
	}