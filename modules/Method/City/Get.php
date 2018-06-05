<?

	namespace Method\City;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use PDO;

	class Get extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `city`");
			$stmt->execute();

			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\City");

			return new ListCount(sizeOf($items), $items);
		}
	}