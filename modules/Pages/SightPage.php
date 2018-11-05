<?

	namespace Pages;

	use Method\APIException;
	use Method\ErrorCode;
	use Model\ListCount;
	use Model\Mark;
	use Model\Params;
	use Model\Photo;
	use Model\Point;
	use Model\User;
	use tools\OpenGraph;

	class SightPage extends BasePage implements RibbonPage {

		protected function prepare($action) {

			$this->addScript("/pages/js/sight-page.js");
			$this->addScript("/pages/js/api.js");
			$this->addScript("/lib/baguetteBox.min.js");

			$id = (int) get("id");

			/** @var Point $info */
			$info = null;

			/** @var User $owner */
			$owner = null;

			/** @var Photo[] $photos */
			$photos = null;

			/** @var ListCount $comments */
			$comments = null;

			/** @var int[] $stats */
			$stats = null;

			/** @var Mark[] $marks */
			$marks = null;
			try {

				$info = $this->mController->perform(new \Method\Point\GetById((new Params)->set("pointId", $id)));

				$name = get("name");
				if ($name && $name !== getTransliteratedNamePlace($info)) {
					throw new APIException(ErrorCode::POINT_NOT_FOUND);
				}

				$owner = $this->mController->perform(new \Method\User\GetById((new Params)->set("userIds", $info->getOwnerId())));

				$args = (new Params)->set("pointId", $id);

				$photos = $this->mController->perform(new \Method\Photo\Get($args));

				$comments = $this->mController->perform(new \Method\Comment\Get($args));

				$stats = $this->mController->perform(new \Method\Point\GetVisitCount($args));

				$marks = $this->mController->perform(new \Method\Mark\GetByPoint($args));

				$this->mOpenGraphInfo = new OpenGraph();

				//$urlLink = htmlspecialchars(sprintf("/map?c=%.6f_%.6f&z=15&id=%d", $info->getLat(), $info->getLng(), $info->getId()));
				//$login = htmlspecialchars($owner->getLogin());

				$this->mOpenGraphInfo->set([
					OpenGraph::KEY_TYPE => OpenGraph::TYPE_ARTICLE,
					OpenGraph::KEY_TITLE => $info->getTitle(),
					OpenGraph::KEY_DESCRIPTION => $info->getDescription(),
					OpenGraph::KEY_IMAGE => $info->getPhoto()
						? $info->getPhoto()->getUrlOriginal()
						: $this->getYandexMapsUrlThumbnailByPlace($info),
					OpenGraph::ARTICLE_PUBLISHED_TIME => $info->getDate(),
					OpenGraph::ARTICLE_MODIFIED_TIME => $info->getDateUpdated(),
					OpenGraph::ARTICLE_AUTHOR => $owner->getFirstName() . " " . $owner->getLastName()
				]);


				$this->mClassBody .= join(" ", [
					$info->isVerified() ? "sight--verified" : "",
					$info->isArchived() ? "sight--archived" : "",
					$info->getCity() ? "sight--withCity" : "",
					$info->getPhoto() ? "sight--withPhoto" : "",
					$this->mController->getUser() && $info->getOwnerId() === $this->mController->getUser()->getId() ? "sight--owner" : "",
				]);



				return [$info, $owner, $photos, $comments, $stats, $marks];
			} catch (APIException $e) {
				return $e;
			}
		}

		public function getJavaScriptInit() {
			$code = <<<CODE
baguetteBox.run(".sight-photos-items", {
	noScrollbars: true,
	async: true
});
CODE;

			return $code;
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			/** @var Point $info */
			list($info) = $data;
			return $info->getTitle();
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			list($info, $owner, $photos, $comments, $stats, $marks) = $data;

			require_once self::$ROOT_DOC_DIR . "sight.content.php";
		}

		public function getRibbonImage($data) {
			/** @var Point $info */
			list($info) = $data;

			if (!$info->getPhoto()) {
				return null;
			}

			return $info->getPhoto()->getUrlOriginal();
		}

		public function getRibbonContent($data) {
			/** @var Point $info */
			/** @var User $owner */
			list($info, $owner) = $data;
			return [
				htmlSpecialChars($info->getTitle()),

				$info->getCity()
					? sprintf("<a href=\"/city/%d\">%s</a>", $info->getCity()->getId(), $info->getCity()->getName())
					: "",

				sprintf("<a href=\"/user/%s\">@%1\$s</a>", $owner->getLogin())
			];
		}

		/**
		 * @param Point $info
		 * @return string
		 */
		private function getYandexMapsUrlThumbnailByPlace($info) {
			return htmlSpecialChars(sprintf("https://static-maps.yandex.ru/1.x/?pt=%.6f,%.6f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1", $info->getLng(), $info->getLat()));
		}
	}