<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;

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
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId is not specified or 'state' value not belongs enumerable VisitState");
			}

			$userId = $main->getSession()->getUserId();

			$args = [":pid" => $this->pointId, ":uid" => $userId, ":sti" => $this->state];

			if ($this->state) {
				$sql = "INSERT INTO `pointVisit` (`pointId`, `userId`, `state`) VALUES (:pid, :uid, :sti) ON DUPLICATE KEY UPDATE `state` = :sti";
			} else {
				$sql = "DELETE FROM `pointVisit` WHERE `pointId` = :pid AND `userId` = :uid";
				unset($args[":sti"]);
			}

			$stmt = $main->makeRequest($sql);
			$stmt->execute($args);

			return [
				"change" => (boolean) $stmt->rowCount(),
				"state" => $main->perform(new GetVisitCount((new Params)->set("pointId", $this->pointId)))
			];
		}
	}