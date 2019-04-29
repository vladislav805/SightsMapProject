<?

	namespace Pages;

	use Method\APIException;
	use Model\Params;
	use Model\User;
	use ObjectController\UserController;
	use tools\OpenGraph;

	class UserPage extends BasePage implements RibbonPage {

		protected function prepare($action) {

			$this->addScript("/pages/js/api.js");
			$this->addScript("/lib/baguetteBox.min.js");

			$id = get("id"); // string (login) will be 0

			$info = null;

			try {
				/** @var \Model\User $info */
				$info = (new UserController($this->mController))->getById($id, ["photo", "city", "rating"]);

				if (!$info) {
					$this->error(404);
				}

				$achievements = $this->mController->perform(new \Method\User\GetUserAchievements(["userId" => $info->getId()]));

				$params = new Params;
				$params
					->set("ownerId", $info->getId())
					->set("offset", (int) get("offset"))
					->set("count", 20);

				/** @var \Model\ListCount $ownPlaces */
				$ownPlaces = $this->mController->perform(new \Method\Sight\GetOwns($params));

				$this->getOpenGraph()->set([
					OpenGraph::KEY_TITLE => "Профиль @" . $info->getLogin(),
					OpenGraph::KEY_DESCRIPTION => $info->getFirstName() . " " . $info->getLastName(),
					OpenGraph::KEY_IMAGE => $info->getPhoto()->getUrlOriginal(),
					OpenGraph::KEY_TYPE => OpenGraph::TYPE_PROFILE,
					OpenGraph::KEY_PROFILE_FIRST_NAME => $info->getFirstName(),
					OpenGraph::KEY_PROFILE_LAST_NAME=> $info->getLastName(),
					OpenGraph::KEY_PROFILE_USERNAME => $info->getLogin(),
					OpenGraph::KEY_PROFILE_GENDER => $info->getSex() === 1 ? OpenGraph::PROFILE_GENDER_FEMALE : OpenGraph::PROFILE_GENDER_MALE
				]);



				return [$info, $ownPlaces, $achievements];
			} catch (APIException $e) {
				return [$info, null, null];
			}
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			/** @var User $info */
			list($info) = $data;
			return "@" . $info->getLogin();
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($info, $places, $achievements) = $data;

			require_once self::$ROOT_DOC_DIR . "user.content.php";
		}

		/**
		 * @param mixed $data
		 * @return boolean
		 */
		public function hasRibbon($data) {
			return true;
		}

		/**
		 * @param mixed $data
		 * @return null
		 */
		public function getRibbonImage($data) {
			return null;
		}

		public function getRibbonContent($data) {
			/** @var User $info */

			list($info, ) = $data;

			$subtitle = [];

			if ($info->getCity()) {
				$subtitle[] = sprintf("<a href=\"/sight/search?cityId=%d\">%s</a>", $info->getCity()->getId(), $info->getCity()->getName());
			}

			$subtitle[] = $this->getLastSeenString($info);

			return [
				htmlSpecialChars($info->getFirstName() . " " . $info->getLastName()),

				"@" . htmlSpecialChars($info->getLogin()),

				join(", ", $subtitle)
			];
		}

		/**
		 * @param User $user
		 * @return string
		 */
		private function getLastSeenString($user) {
			return $user->isOnline()
				? "online"
				: sprintf("%s на сайте %s", getGenderWord($user, "был", "была"), getRelativeDate($user->getLastSeen()));
		}

	}