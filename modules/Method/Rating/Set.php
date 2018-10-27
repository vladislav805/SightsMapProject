<?

	namespace Method\Rating;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\Params;

	/**
	 * Изменение рейтинга места
	 * @package Method\Rating
	 */
	class Set extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int */
		protected $rating;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!is_numeric($this->rating) || !inRange($this->rating, -1, 1) || !$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$args = [
				":pid" => $this->pointId,
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
				"rating" => $main->perform(new Get((new Params)->set("pointId", $this->pointId)))
			];
		}
	}