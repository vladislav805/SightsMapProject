<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Изменение состояния посещения пользователем места
	 * @package Method\Point
	 */
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
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId || !inRange($this->state, VisitState::NOT_VISITED, VisitState::DESIRED)) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$userId = $main->getSession()->getUserId();

			if ($this->state) {
				$sql = "INSERT INTO `pointVisit` (`pointId`, `userId`, `state`) VALUES (:pid, :uid, :sti) ON DUPLICATE KEY UPDATE `state` = :sti";
			} else {
				$sql = "DELETE FROM `pointVisit` WHERE `pointId` = :pid AND `userId` = :uid OR :sti = :sti";
			}

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":pid" => $this->pointId, ":uid" => $userId, ":sti" => $this->state]);

			return (boolean) $stmt->rowCount();
		}
	}