<?

	namespace Pages;

	use Method\APIException;
	use Method\Point\GetRandomPlace;
	use Model\Params;
	use Model\Point;
	use Model\User;
	use tools\OpenGraph;

	class RandomSightPage extends BasePage implements VirtualPage {

		protected function prepare($action) {
			/** @var Point $sight */
			/** @noinspection PhpUnhandledExceptionInspection */
			$sight = $this->mController->perform(new GetRandomPlace(new Params));

			redirectTo(sprintf("/place/%d", $sight->getId()));
		}

		public function getBrowserTitle($data) {
			return null;
		}

		public function getPageTitle($data) {
			return null;
		}

		public function getContent($data) {}

	}