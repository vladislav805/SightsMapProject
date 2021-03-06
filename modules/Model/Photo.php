<?

	namespace Model;

	class Photo implements IItem, IOwnerable, IDateable {

		use APIModelGetterFields;

		const DEFAULT_USER_PHOTO = "https://" . DOMAIN_MEDIA . "/none.png";

		const TYPE_EMPTY = 0;
		const TYPE_SIGHT = 1;
		const TYPE_PROFILE = 2;
		const TYPE_SIGHT_SUGGESTED = 3;

		/** @var int */
		private $ownerId = 0;

		/** @var int */
		private $photoId = 0;

		/** @var int */
		private $date = 0;

		/** @var string */
		private $path = "/";

		/** @var string */
		private $nameThumbnail;

		/** @var string */
		private $nameOriginal;

		/** @var string */
		private $urlThumbnail = self::DEFAULT_USER_PHOTO;

		/** @var string */
		private $urlOriginal = self::DEFAULT_USER_PHOTO;

		/** @var int */
		private $type = 0;

		/** @var double */
		private $latitude;

		/** @var double */
		private $longitude;

		/** @var int */
		private $width;

		/** @var int */
		private $height;

		/**
		 * UserPhoto constructor.
		 * @param array $p
		 */
		public function __construct($p) {
			isset($p["ownerId"]) && ($this->ownerId = (int) $p["ownerId"]);
			isset($p["photoId"]) && ($this->photoId = (int) $p["photoId"]);
			isset($p["date"]) && ($this->date = (int) $p["date"]);
			isset($p["path"]) && ($this->path = $p["path"]);
			isset($p["photo200"]) && ($this->urlThumbnail = $this->getPhotoURL($this->nameThumbnail = $p["photo200"]));
			isset($p["photoMax"]) && ($this->urlOriginal = $this->getPhotoURL($this->nameOriginal = $p["photoMax"]));
			isset($p["type"]) && ($this->type = (int) $p["type"]);
			isset($p["latitude"]) && ($this->latitude = (double) $p["latitude"]);
			isset($p["longitude"]) && ($this->longitude = (double) $p["longitude"]);
			isset($p["width"]) && ($this->width = (int) $p["width"]);
			isset($p["height"]) && ($this->height = (int) $p["height"]);
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
			return $url ? "https://" . DOMAIN_MEDIA . "/" . $this->path . "/" . $url : self::DEFAULT_USER_PHOTO;
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
		 * @return int
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * @return float
		 */
		public function getLatitude() {
			return $this->latitude;
		}

		/**
		 * @return float
		 */
		public function getLongitude() {
			return $this->longitude;
		}

		/**
		 * @return array
		 */
		public function jsonSerialize() {
			$r = [
				"ownerId" => $this->ownerId,
				"photoId" => $this->photoId,
				"date" => $this->date,
				"photo200" => $this->urlThumbnail,
				"photoMax" => $this->urlOriginal,
				"type" => $this->type
			];

			if ($this->latitude && $this->longitude) {
				$r["latitude"] = $this->latitude;
				$r["longitude"] = $this->longitude;
			}

			if ($this->width && $this->height) {
				$r["width"] = $this->width;
				$r["height"] = $this->height;
			}

			if ($this->type === self::TYPE_EMPTY) {
				$r["isStandard"] = true;
			}
			return $r;
		}

	}