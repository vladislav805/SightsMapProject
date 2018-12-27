<?

	namespace Method\Sight;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Sight;
	use PDO;

	/**
	 * Возвращает рандомное/случайное место
	 * @package Method\Point
	 */
	class GetRandomPlace extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return Sight
		 */
		public function resolve(IController $main) {
			$res = $main->getDatabaseProvider()->query("SELECT COUNT(*) FROM `point`");
			$res->execute();
			list($count) = $res->fetch(PDO::FETCH_NUM);

			$stmt = $main->getDatabaseProvider()->query(sprintf("SELECT `pointId` FROM `point` LIMIT %d, 1", rand(0, $count - 1)));
			$stmt->execute();

			list($pointId) = $stmt->fetch(PDO::FETCH_NUM);

			return $main->perform(new GetById(["sightId" => (int) $pointId]));
		}
	}