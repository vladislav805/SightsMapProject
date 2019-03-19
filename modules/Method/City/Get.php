<?

	namespace Method\City;

	use Method\APIPublicMethod;
	use Model\IController;
	use ObjectController\CityController;

	class Get extends APIPublicMethod {

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			return (new CityController($main))->get(null);
		}
	}