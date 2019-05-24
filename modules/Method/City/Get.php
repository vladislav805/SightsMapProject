<?

	namespace Method\City;

	use Method\APIPublicMethod;
	use Model\City;
	use Model\IController;
	use ObjectController\CityController;

	class Get extends APIPublicMethod {

		/** @var string */
		protected $extra = [];

		/**
		 * @param IController $main
		 * @return City
		 */
		public function resolve(IController $main) {
			if (is_string($this->extra)) {
				$this->extra = explode(",", $this->extra);
			}
			return (new CityController($main))->get(null, 0, 0, $this->extra);
		}
	}