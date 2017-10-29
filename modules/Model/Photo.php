<?

	namespace Model;

	class Photo implements \IItem, \IOwnerable, \IDateable {

		const DEFAULT_USER_PHOTO = "http://" . DOMAIN . "/userdata/none.png";

		const TYPE_POINT = 1;
		const TYPE_PROFILE = 2;

		/** @var int */
		private $ownerId;

		/** @var int */
		private $photoId;

		/** @var int */
		private $date;

		/** @var string */
		private $path;

		/** @var string */
		private $nameThumbnail;

		/** @var string */
		private $nameOriginal;

		/** @var string */
		private $urlThumbnail = self::DEFAULT_USER_PHOTO;

		/** @var string */
		private $urlOriginal = self::DEFAULT_USER_PHOTO;

		/** @var int */
		private $type;

		/**
		 * UserPhoto constructor.
		 * @param array $p
		 */
		public function __construct($p) {
			$this->ownerId = (int) $p["ownerId"];
			$this->photoId = (int) $p["photoId"];
			$this->date = (int) $p["date"];
			$this->path = $p["path"];
			$this->urlThumbnail = $this->getPhotoURL($this->nameThumbnail = $p["photo200"]);
			$this->urlOriginal = $this->getPhotoURL($this->nameOriginal = $p["photoMax"]);
			$this->type = (int) $p["type"];
		}

		/**
		 * @return int
		 */
		public function getId() {
			return $this->photoId;
		}

		/**
		 * @return int
		 */
		public function getOwnerId() {
			return $this->ownerId;
		}

		/**
		 * @return int
		 */
		public function getDate() {
			return $this->date;
		}

		/**
		 * @param string $url
		 * @return string
		 */
		private function getPhotoURL($url) {
			return $url ? "http://" . DOMAIN . "/userdata/" . $this->path . "/" . $url : self::DEFAULT_USER_PHOTO;
		}

		/**
		 * @return string
		 */
		public function getUrlThumbnail() {
			return $this->urlThumbnail;
		}

		/**
		 * @return string
		 */
		public function getUrlOriginal() {
			return $this->urlOriginal;
		}

		/**
		 * @return string
		 */
		public function getPath() {
			return $this->path;
		}

		/**
		 * @return string
		 */
		public function getNameOriginal() {
			return $this->nameOriginal;
		}

		/**
		 * @return string
		 */
		public function getNameThumbnail() {
			return $this->nameThumbnail;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			return [
				"ownerId" => $this->ownerId,
				"photoId" => $this->photoId,
				"date" => $this->date,
				"photo200" => $this->urlThumbnail,
				"photoMax" => $this->urlOriginal,
				"type" => $this->type
			];
		}

	}