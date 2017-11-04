<?php

	namespace Method\Point;

	use APIException;
	use APIModeratorMethod;
	use IController;
	use Model\Event;
	use Model\Point;
	use Params;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			/** @var Point $point */
			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));



			$sql = sprintf("UPDATE `point` SET `isVerified` = '%d' WHERE `pointId` = '%d' LIMIT 1", $this->state, $point->getId());
			$this->state && \Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_VERIFIED, $this->pointId);
			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}