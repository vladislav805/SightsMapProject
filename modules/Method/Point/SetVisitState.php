<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class SetVisitState extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int */
		protected $state = VisitState::VISITED;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId || !inRange($this->state, VisitState::NOT_VISITED, VisitState::DESIRED)) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$ownerId = $main->getSession()->getUserId();

			if ($this->state) {
				$sql = sprintf("INSERT INTO `pointVisit` (`pointId`, `userId`, `state`) VALUES ('%d', '%d', '%d') ON DUPLICATE KEY UPDATE `state` = '%3\$d'", $this->pointId, $ownerId, $this->state);
			} else {
				$sql = sprintf("DELETE FROM `pointVisit` WHERE `pointId` = '%d' AND `userId` = '%d' LIMIT 1", $this->pointId, $ownerId);
			}

			$db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			/** @var Point $p */
			$p = $main->perform(new GetById(["pointId" => $this->pointId]));
			$p->setVisitState($this->state);

			return $p;
		}
	}