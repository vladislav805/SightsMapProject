<?

	namespace Method\Rating;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение рейтинга места
	 * @package Method\Rating
	 */
	class Set extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $rating;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!is_numeric($this->rating) || !inRange($this->rating, -1, 1) || !$this->sightId) {
				throw new APIException(ErrorCode::RATING_INVALID, null, "Argument rating must be -1, 0, or 1");
			}

			$args = [
				":pid" => $this->sightId,
				":uid" => $main->getSession()->getUserId(), // TODO: authKey
				":rid" => $this->rating
			];

			if ($this->rating) {
				$stmt = $main->makeRequest("INSERT INTO `rating` (`pointId`, `userId`, `rate`) VALUES (:pid, :uid, :rid) ON DUPLICATE KEY UPDATE `rate` = :rid");
			} else {
				$stmt = $main->makeRequest("DELETE FROM `rating` WHERE `pointId` = :pid AND `userId` = :uid");
				unset($args[":rid"]);
			}

			$stmt->execute($args);

			return [
				"change" => (boolean) $stmt->rowCount(),
				"rating" => $main->perform(new Get(["sightId" => $this->sightId]))
			];
		}
	}