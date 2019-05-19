<?php

	namespace Method\Sight;

	use Constant\VisitState;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение состояния посещения пользователем места
	 * @package Method\Sight
	 */
	class SetVisitState extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $state = VisitState::VISITED;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->sightId || !VisitState::inRange($this->state)) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId is not specified or 'state' value not belongs enumerable VisitState");
			}

			$userId = $main->getSession()->getUserId();

			$args = [":sid" => $this->sightId, ":uid" => $userId, ":sti" => $this->state];

			if ($this->state) {
				$sql = "INSERT INTO `sightVisit` (`sightId`, `userId`, `state`) VALUES (:sid, :uid, :sti) ON DUPLICATE KEY UPDATE `state` = :sti";
			} else {
				$sql = "DELETE FROM `sightVisit` WHERE `sightId` = :sid AND `userId` = :uid";
				unset($args[":sti"]);
			}

			$stmt = $main->makeRequest($sql);
			$stmt->execute($args);

			return [
				"change" => (boolean) $stmt->rowCount(),
				"state" => $main->perform(new GetVisitCount(["sightId" => $this->sightId]))
			];
		}
	}