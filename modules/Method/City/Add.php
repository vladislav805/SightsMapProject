<?

	namespace Method\City;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\StandaloneCity;
	use ObjectController\CityController;

	class Add extends APIModeratorMethod {

		/** @var string */
		protected $name;

		/** @var int|null */
		protected $parentId;

		/** @var float|null */
		protected $lat;

		/** @var float|null */
		protected $lng;

		/** @var int */
		protected $radius;

		/** @var string */
		protected $description;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			if (!inRange(mb_strlen($this->name), 2, 32) || !isCoordinate($this->lat, $this->lng)) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$city = new StandaloneCity([
				"name" => $this->name,
				"parentId" => $this->parentId,
				"lat" => $this->lat,
				"lng" => $this->lng,
				"radius" => $this->radius,
				"description" => $this->description
			]);

			return (new CityController($main))->add($city);
		}
	}