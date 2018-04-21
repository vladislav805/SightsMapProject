<?

	namespace Method\Rating;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Get extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		/**
		 * Realization of some action
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$d = $db->query(sprintf("SELECT AVG(`rate`) FROM `rating` WHERE `pointId` = %d", $this->pointId), DatabaseResultType::ITEM);
			return (float) $d["AVG(`rate`)"];
		}
	}