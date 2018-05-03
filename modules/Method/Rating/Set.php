<?

	namespace Method\Rating;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;

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
			if (!is_numeric($this->rating) || !inRange($this->rating, 1, 10) || !$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$stmt = $main->makeRequest("INSERT INTO `rating` (`pointId`, `userId`, `rate`) VALUES (:pid, :uid, :rid) ON DUPLICATE KEY UPDATE `rate` = :rid");
			$stmt->execute([
				":pid" => $this->pointId,
				":uid" => $main->getSession()->getUserId(), // TODO: authKey
				":rid" => $this->rating
			]);

			return (boolean) $stmt->rowCount(); // TODO: возможно, возвращать новое значение рейтинга?
		}
	}