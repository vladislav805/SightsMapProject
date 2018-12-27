<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Model\Event;
	use Model\IController;
	use Model\Params;
	use Model\Sight;

	/**
	 * Изменение верификации места
	 * @package Method\Point
	 */
	class SetVerify extends APIModeratorMethod {

		/** @var int */
		protected $sightId;

		/** @var boolean */
		protected $state;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			/** @var Sight $point */
			$point = $main->perform(new GetById((new Params)->set("sightId", $this->sightId)));

			$stmt = $main->makeRequest("UPDATE `point` SET `isVerified` = ? WHERE `pointId` = ? LIMIT 1");
			$stmt->execute([$this->state, $point->getId()]);

			$this->state && \Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_VERIFIED, $this->sightId);
			return (boolean) $stmt->rowCount();
		}
	}