<?

	namespace Method\City;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\City;
	use Model\IController;
	use PDO;

	class Add extends APIModeratorMethod {

		/** @var string */
		private $name;

		/** @var int|null */
		private $parentId;

		/** @var float|null */
		private $lat;

		/** @var float|null */
		private $lng;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			if (!inRange(mb_strlen($this->name), 2, 32)) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$stmt = $main->makeRequest("INSERT INTO `city` (`name`, `parentId`, `lat`, `lng`) VALUES (?, ?, ?, ?)");
			$stmt->execute([$this->name, $this->parentId, $this->lat, $this->lng]);

			$cityId = $main->getDatabaseProvider()->lastInsertId();

			$stmt = $main->makeRequest("SELECT * FROM `city` WHERE `cityId` = ?");
			$stmt->execute([$cityId]);

			$city = new City($stmt->fetch(PDO::FETCH_ASSOC));

			return $city;
		}
	}