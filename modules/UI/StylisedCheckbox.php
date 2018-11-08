<?

	namespace UI;

	class StylisedCheckbox implements UIElement {

		/** @var string */
		private $name;

		/** @var string */
		private $value;

		/** @var string */
		private $id;

		/** @var boolean */
		private $state;

		/** @var string */
		private $label;

		/** @var string */
		private $color;

		/**
		 * StylisedCheckbox constructor.
		 * @param string $name
		 * @param string $label
		 * @param boolean $state
		 * @param string|int $value
		 * @param string|null $id
		 * @param string|null $color
		 */
		public function __construct($name, $label, $state, $value = "1", $id = null, $color = null) {
			$this->name = $name;
			$this->value = $value;
			$this->label = $label;
			$this->state = $state;
			$this->id = $id === null ? md5($name) : $id;
			$this->color = $color;
		}

		/**
		 * @param string $label
		 * @return StylisedCheckbox
		 */
		public function setLabel($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param boolean $state
		 * @return StylisedCheckbox
		 */
		public function setState($state) {
			$this->state = $state;
			return $this;
		}

		/**
		 * @return string
		 */
		public function __toString() {
			/** @noinspection HtmlUnknownAttribute */
			$res = "<label class=\"fi-checkbox %6\$s\" %7\$s>
					<input type=\"checkbox\" name=\"%1\$s\" value=\"%2\$s\" id=\"%3\$s\" %s />
					<span>%4\$s</span>
				</label>";


			return sprintf(
				$res,
				$this->name,
				$this->value,
				$this->id,
				htmlSpecialChars($this->label),
				$this->state ? " checked" : "",
				$this->color ? " fi-checkbox-colorized" : "",
				$this->color ? sprintf(" style=\"--colorMark: #%s;?>\"", $this->color) : ""
			);
		}
	}