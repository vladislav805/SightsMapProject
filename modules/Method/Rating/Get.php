<?

	namespace Method\Rating;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение рейтинга места
	 * @deprecated Использовать points.get и свойство rating
	 * @package Method\Rating
	 */
	class Get extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return float
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT AVG(`rate`) AS `rate` FROM `rating` WHERE `pointId` = ?");
			$stmt->execute([$this->pointId]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (float) $result["rate"];
		}
	}