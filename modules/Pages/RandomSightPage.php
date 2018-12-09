<?

	namespace Pages;

	use Method\Point\GetRandomPlace;
	use Model\Params;
	use Model\Point;

	class RandomSightPage extends BasePage implements VirtualPage {

		protected function prepare($action) {
			/** @var Point $sight */
			/** @noinspection PhpUnhandledExceptionInspection */
			$sight = $this->mController->perform(new GetRandomPlace(new Params));

			redirectTo(sprintf("/sight/%d", $sight->getId()));
		}

		public function getBrowserTitle($data) {
			return null;
		}

		public function getPageTitle($data) {
			return null;
		}

		public function getContent($data) {}

	}