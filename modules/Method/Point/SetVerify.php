<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Model\IController;
	use Model\Event;
	use Model\Point;
	use Model\Params;

	/**
	 * Изменение верификации места
	 * @package Method\Point
	 */
	class SetVerify extends APIModeratorMethod {

		/** @var int */
		protected $pointId;

		/** @var boolean */
		protected $state;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			/** @var Point $point */
			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			$stmt = $main->makeRequest("UPDATE `point` SET `isVerified` = ? WHERE `pointId` = ? LIMIT 1");
			$stmt->execute([$this->state, $point->getId()]);

			$this->state && \Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_VERIFIED, $this->pointId);
			return (boolean) $stmt->rowCount();
		}
	}