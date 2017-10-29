<?
	namespace tools;

	use RuntimeException;

	class SingleImage {

		/** @var string */
		private $filename;

		/** @var resource */
		private $image;

		/** @var int */
		private $imageType;

		/**
		 * Image constructor.
		 * @param string $filename
		 */
		public function __construct($filename) {
			$this->filename = $filename;
			$this->load();
		}

		/**
		 * Get image meta data
		 */
		private function load() {
			$imageType = getImageSize($this->filename);
			$this->imageType = $imageType[2];
			switch ($this->imageType) {

				case IMAGETYPE_JPEG:
					$this->image = imageCreateFromJPEG($this->filename);
					$this->fixExifOrientation();
					break;

				case IMAGETYPE_GIF:
					$this->image = imageCreateFromGIF($this->filename);
					break;

				case IMAGETYPE_PNG:
					$this->image = imageCreateFromPNG($this->filename);
					break;

				default:
					throw new RuntimeException;

			};

			return $this;
		}

		/**
		 * Fix image rotating by EXIF-meta records
		 */
		private function fixExifOrientation() {
			$exif = exif_read_data($this->filename);
			if (!empty($exif["Orientation"])) {
				$angle = $exif["Orientation"];
				switch ($angle) {
					case 8:
						$this->image = imageRotate($this->image,90,0);
						break;
					case 3:
						$this->image = imageRotate($this->image,180,0);
						break;
					case 6:
						$this->image = imageRotate($this->image,-90,0);
						break;
				}
			}
		}

		/**
		 * Save image
		 * @param string $filename File name
		 * @param int $imageType Type/format
		 * @param int $compression Quality
		 * @param int $permissions Chmod
		 * @return boolean
		 */
		public function save($filename, $imageType = IMAGETYPE_JPEG, $compression = 95, $permissions = null) {
			switch ($imageType) {

				case IMAGETYPE_JPEG:
					$result = imageJPEG($this->image, $filename, $compression);
					break;

				case IMAGETYPE_GIF:
					$result = imageGIF($this->image, $filename);
					break;

				case IMAGETYPE_PNG:
					$result = imagePNG($this->image, $filename);
					break;

				default:
					throw new RuntimeException;

			};

			if ($result && $permissions) {
				chmod($filename, $permissions);
			}

			return $result;
		}

		/**
		 * Output image to client/browser
		 * @param int $imageType
		 */
		public function output($imageType = IMAGETYPE_JPEG) {
			switch ($imageType) {

				case IMAGETYPE_JPEG:
					imageJPEG($this->image);
					break;

				case IMAGETYPE_GIF:
					imageGIF($this->image);
					break;

				case IMAGETYPE_PNG:
					imagePNG($this->image);
					break;

				default:
					throw new RuntimeException;

			};
		}

		/**
		 * Returns extension for file
		 * @return string
		 */
		public function getExtension() {
			switch ($this->imageType) {
				case IMAGETYPE_JPEG: return "jpg";
				case IMAGETYPE_GIF: return "gif";
				case IMAGETYPE_PNG: return "png";
				default: return false;
			}
		}

		/**
		 * Returns width of image
		 * @return int
		 */
		public function getWidth () {
			return imageSX($this->image);
		}

		/**
		 * Returns height of image
		 * @return int
		 */
		public function getHeight() {
			return imageSY($this->image);
		}

		/**
		 * Resize image to specified height with proportional width
		 * @param int $height
		 * @return $this
		 */
		public function resizeToHeight($height) {
			$ratio = $height / $this->getHeight();
			$width = $this->getWidth() * $ratio;
			$this->resize($width, $height);
			return $this;
		}

		/**
		 * Resize image to specified width with proportional height
		 * @param int $width
		 * @return $this
		 */
		public function resizeToWidth($width) {
			$ratio = $width / $this->getWidth();
			$height = $this->getheight() * $ratio;
			$this->resize($width, $height);
			return $this;
		}

		/**
		 * Resize image to scale
		 * @param double $scale
		 * @return $this
		 */
		public function setScale($scale) {
			$width = $this->getWidth() * $scale / 100;
			$height = $this->getheight() * $scale / 100;
			$this->resize($width, $height);
			return $this;
		}

		/**
		 * Resize image to specified sizes
		 * @param int $width
		 * @param int $height
		 * @return $this
		 */
		public function resize($width, $height) {
			$image = imageCreateTrueColor($width, $height);
			imageCopyReSampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
			$this->image = $image;
			return $this;
		}

		/**
		 * Resize image to specified size of max length one of side
		 * @param int $size
		 * @return SingleImage
		 */
		public function resizeToMaxSizeSide($size) {
			return $this->getWidth() > $this->getHeight() ? $this->resizeToWidth($size) : $this->resizeToHeight($size);
		}

		public function drawText(ImageText $text, $opt = array()) {
			$text->draw($this, $opt);
			return $this;
		}

		public function drawRect($x1, $y1, $x2, $y2, $color) {
			imageFilledRectAngle($this->image, $x1, $y1, $x2, $y2, $color);
			return $this;
		}

		/**
		 *
		 */
		public function __destruct() {
			imageDestroy($this->image);
		}

		public function getImage() {
			return $this->image;
		}

	}