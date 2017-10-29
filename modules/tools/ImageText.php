<?

	namespace tools;

	use Exception;

	class ImageText {

		/** @var int */
		protected $x = 0;

		/** @var int */
		protected $y = 0;

		/** @var string */
		protected $text = "";

		/** @var int */
		protected $color = 0x000000;

		/** @var string */
		protected $fontFace;

		/** @var string */
		protected $fontSize = 11;

		/** @var int */
		protected $width = false;

		/**
		 * ImageText constructor.
		 * @param int    $x
		 * @param int    $y
		 * @param string $text
		 */
		public function __construct($x, $y, $text = "") {
			$this->x = $x;
			$this->y = $y;
			$this->text = $text;
		}

		/**
		 * @param string $fontFace
		 * @return $this
		 */
		public function setFontFace($fontFace) {
			$this->fontFace = $fontFace;
			return $this;
		}

		/**
		 * @param string $fontSize
		 * @return $this
		 */
		public function setFontSize($fontSize) {
			$this->fontSize = $fontSize;
			return $this;
		}

		/**
		 * @param string $text
		 * @return $this
		 */
		public function setText($text) {
			$this->text = $text;
			return $this;
		}

		/**
		 * @param int $width
		 * @return $this
		 */
		public function setWidth($width) {
			$this->width = $width;
			return $this;
		}

		/**
		 * @param int $color
		 * @return $this
		 */
		public function setColor($color) {
			$this->color = $color;
			return $this;
		}

		/**
		 * @param SingleImage $image
		 * @param array       $opt
		 * @return $this
		 * @throws Exception
		 */
		public function draw(SingleImage $image, $opt = array()) {
			$text = isset($opt["width"]) ? $this::wrapTextString($this->text, $this->fontFace, $this->fontSize, $opt["width"]) : $this->text;

			imageTTFText($image->getImage(), $this->fontSize, 0, $this->x, $this->y, $this->color, $this->fontFace, $text);
			return $this;
		}

		/**
		 * Return string, which will be
		 * @param string $string
		 * @param int $fontSize
		 * @param string $fontFace
		 * @param int $width
		 * @return string
		 */
		public static function wrapTextString($string, $fontFace, $fontSize, $width) {

			$result = "";
			$words = explode(" ", $string);


			foreach ($words as $word) {
				$testBoxWord = imageTTFBbox($fontSize, 0, $fontFace, $word);

				$length = mb_strLen($word);

				while ($testBoxWord[2] > $width && $length > 0) {
					$word = mb_subStr($word, 0, $length);
					$length--;
					$testBoxWord = imageTTFBbox($fontSize, 0, $fontFace, $word);
				}

				$testString = $result . " " . $word;
				$testBoxString = imageTTFBbox($fontSize, 0, $fontFace, $testString);

				if ($testBoxString[2] > $width) {
					$result .= ($result == "" ? "" : "\n") . $word;
				} else {
					$result .= ($result == "" ? "" : " ") . $word;
				}
			}

			return $result;
		}


		public static function getTextDimens($string, $fontFace, $fontSize) {
			$sizes = imageTTFBbox($fontSize, 0, $fontFace, $string);
			return ["width" => $sizes[4] - $sizes[6], "height" => $sizes[1] - $sizes[7]];
		}

	}

