<?

	namespace Method\City;

	use Method\APIPublicMethod;
	use Model\City;
	use Model\IController;
	use PDO;

	class GetById extends APIPublicMethod {

		/** @var int[] */
		protected $cityIds;

		/**
		 * @param IController $main
		 * @return City[]
		 */
		public function resolve(IController $main) {
			$cid = $this->cityIds;

			if (is_string($cid)) {
				$cid = explode(",", $cid);
			}

			$list = array_unique(array_map("intval", $cid));
			$stmt = $main->makeRequest("SELECT * FROM `city` WHERE `cityId` IN (" . join(",", $list) . ")");
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\City");

			return $items;
		}
	}