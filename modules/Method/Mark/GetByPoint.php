<?

	namespace Method\Mark;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Mark;
	use PDO;

	class GetByPoint extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/**
		 * @param IController $main
		 * @return Mark[]
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `mark`, `pointMark` WHERE `mark`.`markId` = `pointMark`.`markId` AND `pointMark`.`pointId` = ?");
			$stmt->execute([$this->sightId]);
			return parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Mark");
		}
	}