<?

	namespace Method\City;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\CityController;

	class Remove extends APIModeratorMethod {

		/** @var int */
		protected $cityId;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			if (!$this->cityId) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			$ctl = new CityController($main);

			$res = $ctl->getByIds([$this->cityId]);

			if (!$res || !sizeof($res)) {
				throw new APIException(ErrorCode::CITY_NOT_FOUND);
			}

			list($city) = $res;

			return $ctl->remove($city);
		}
	}