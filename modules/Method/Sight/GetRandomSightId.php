<?

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;

	/**
	 * Возвращает рандомное/случайное место
	 * @package Method\Sight
	 */
	class GetRandomSightId extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
			$res = $main->getDatabaseProvider()->query("SELECT getRandomSightId()");
			$res->execute();
			list($sightId) = $res->fetch(PDO::FETCH_NUM);
			return (int) $sightId;
		}
	}