<?

	namespace Pages;

	use Method\Sight\GetRandomPlace;
	use Model\Params;
	use Model\Sight;

	class RandomSightPage extends BasePage implements VirtualPage {

		protected function prepare($action) {
			/** @var Sight $sight */
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