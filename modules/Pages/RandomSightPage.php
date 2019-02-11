<?

	namespace Pages;

	use Method\Sight\GetRandomSightId;

	class RandomSightPage extends BasePage implements VirtualPage {

		protected function prepare($action) {
			$sightId = $this->mController->perform(new GetRandomSightId([]));

			redirectTo(sprintf("/sight/%d", $sightId));
		}

		public function getBrowserTitle($data) {
			return null;
		}

		public function getPageTitle($data) {
			return null;
		}

		public function getContent($data) {}

	}