<?

	namespace Pages;

	use Method\APIException;
	use Method\ErrorCode;
	use Model\ListCount;
	use Model\Mark;
	use Model\Params;
	use Model\Photo;
	use Model\Sight;
	use Model\User;
	use tools\OpenGraph;

	class SightPage extends BasePage implements RibbonPage {

		protected function prepare($action) {

			$this->addScript("/pages/js/sight-page.js");
			$this->addScript("/pages/js/api.js");
			$this->addScript("/pages/js/comments.js");
			$this->addScript("/pages/js/ui/modal.js");
			$this->addScript("/pages/js/ui/toast.js");
			$this->addScript("/lib/baguetteBox.min.js");

			$id = (int) get("id");

			/** @var Sight $info */
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

				$info = $this->mController->perform(new \Method\Sight\GetById((new Params)->set("sightId", $id)));

				$name = get("name");
				if ($name && $name !== getTransliteratedNamePlace($info)) {
					throw new APIException(ErrorCode::SIGHT_NOT_FOUND);
				}

				list($owner) = $this->mController->perform(new \Method\User\GetByIds(["userIds" => [$info->getOwnerId()]]));

				$args = (new Params)->set("sightId", $id);

				$photos = $this->mController->perform(new \Method\Photo\Get($args));

				$comments = $this->mController->perform(new \Method\Comment\Get($args));

				$stats = $this->mController->perform(new \Method\Sight\GetVisitCount($args));

				$marks = $this->mController->perform(new \Method\Mark\GetByPoint($args));

				$this->mOpenGraphInfo = new OpenGraph();
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

				$cls = [
					$info->isVerified() ? "sight--verified" : null,
					$info->isArchived() ? "sight--archived" : null,
					$info->getCity() ? "sight--withCity" : null,
					$info->getPhoto() ? "sight--withPhoto" : null,
					$this->mController->getUser() && $info->getOwnerId() === $this->mController->getUser()->getId() ? "sight--owner" : null
				];

				foreach ($cls as $cl) {
					$cl !== null && $this->addClassBody($cl);
				}

				return [$info, $owner, $photos, $comments, $stats, $marks];
			} catch (APIException $e) {
				if ($e->getCode() === ErrorCode::SIGHT_NOT_FOUND) {
					$this->error(404);
				}
				return $e;
			}
		}

		public function getJavaScriptInit($data) {
			$code = <<<CODE
baguetteBox.run(".sight-photos-items", {
	noScrollbars: true,
	async: true
});
Comments.init();
CODE;

			return $code;
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			/** @var Sight $info */
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
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($info, $owner, $photos, $comments, $stats, $marks) = $data;

			require_once self::$ROOT_DOC_DIR . "sight.content.php";
		}

		public function getRibbonImage($data) {
			/** @var Sight $info */
			list($info) = $data;

			if (!$info->getPhoto()) {
				return null;
			}

			return $info->getPhoto()->getUrlOriginal();
		}

		public function getRibbonContent($data) {
			/** @var Sight $info */
			/** @var User $owner */
			list($info, $owner) = $data;

			/** @noinspection PhpFormatFunctionParametersMismatchInspection */
			return [
				htmlSpecialChars($info->getTitle()),

				$info->getCity()
					? sprintf("<a href=\"/sight/search?cityId=%d\">%s</a>", $info->getCity()->getId(), $info->getCity()->getName())
					: "",

				sprintf("<a href=\"/user/%s\">@%1\$s</a>", $owner->getLogin())
			];
		}

		/**
		 * @param Sight $info
		 * @return string
		 */
		private function getYandexMapsUrlThumbnailByPlace($info) {
			return htmlSpecialChars(sprintf("https://static-maps.yandex.ru/1.x/?pt=%.6f,%.6f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1", $info->getLng(), $info->getLat()));
		}
	}