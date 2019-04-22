<?

	namespace Method\City;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\City;
	use Model\IController;
	use ObjectController\CityController;

	class Edit extends APIModeratorMethod {

		/** @var int */
		protected $cityId;

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
			if (!$this->cityId) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			if (!inRange(mb_strlen($this->name), 2, 32) || !isCoordinate($this->lat, $this->lng)) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$ctl = new CityController($main);

			$res = $ctl->getByIds([$this->cityId]);

			if (!$res || !sizeof($res)) {
				throw new APIException(ErrorCode::CITY_NOT_FOUND);
			}

			/**
			 * @var City $city
			 */
			list($city) = $res;

			$city->setName($this->name)
				->setParentId($this->parentId)
				->setDescription($this->description)
				->setRadius($this->radius)
				->setLat($this->lat)
				->setLng($this->lng);

			return $ctl->edit($city);
		}
	}