<?

	namespace Pages;

	use Method\APIException;
	use Method\ErrorCode;
	use Method\Execute\Compile;
	use Model\ListCount;
	use Model\Mark;
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
$executeCode = <<<CODE
id=getArg id;
s=call sights.getById -sightId \$id;
o=call users.get -userIds \$s/ownerId;
u=\$o/0;
o=\$u/userId;
p=call photos.get -sightId \$id;
c=call comments.get -sightId \$id;
st=call sights.getVisitCount -sightId \$id;
m=call marks.getById -markIds \$s/markIds;
r=new object;
set \$r -f sight,owner,photos,comments,stat,marks -v \$s,\$u,\$p,\$c,\$st,\$m;
ret \$r 
CODE;
				$_REQUEST["id"] = $id; // FIXME: жесткий костыль
				$data = $this->mController->perform(new Compile(["code" => $executeCode]));

				list($info, $owner, $photos, $comments, $stats, $marks) = array_values($data);

				$this->getOpenGraph()->set([
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
				$this->getOpenGraph()->addMeta(OpenGraph::KEY_DESCRIPTION, $info->getTitle() . " &mdash; " . $info->getDescription());

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