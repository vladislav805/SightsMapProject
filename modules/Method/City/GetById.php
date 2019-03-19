<?

	namespace Method\City;

	use Method\APIPublicMethod;
	use Model\City;
	use Model\IController;
	use ObjectController\CityController;

	class GetById extends APIPublicMethod {

		/** @var int[] */
		protected $cityIds;

		/**
		 * @param IController $main
		 * @return City[]
		 */
		public function resolve(IController $main) {
			return (new CityController($main))->getByIds(prepareIds($this->cityIds, PREPARE_INTS));
		}
	}