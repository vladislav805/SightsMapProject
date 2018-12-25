<?

	namespace Method\Rating;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение рейтинга места
	 * @package Method\Rating
	 */
	class Get extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return float
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT SUM(`rate`) AS `rate` FROM `rating` WHERE `pointId` = ?");
			$stmt->execute([$this->sightId]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (float) $result["rate"];
		}
	}