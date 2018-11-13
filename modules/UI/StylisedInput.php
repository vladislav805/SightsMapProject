<?

	namespace UI;

	class StylisedInput implements UIElement {

		/** @var string */
		private $name;

		/** @var string */
		private $type = "text";

		/** @var string */
		private $id;

		/** @var string */
		private $value;

		/** @var string */
		private $label;

		/** @var boolean */
		private $isRequired = true;

		public function __construct($name, $label, $id = null, $value = "") {
			$this->name = $name;
			$this->label = $label;
			$this->id = $id === null ? md5($name) : $id;
			$this->value = $value;
		}

		/**
		 * @param boolean $isRequired
		 * @return StylisedInput
		 */
		public function setIsRequired($isRequired) {
			$this->isRequired = $isRequired;
			return $this;
		}

		/**
		 * @param string $type
		 * @return StylisedInput
		 */
		public function setType($type) {
			$this->type = $type;
			return $this;
		}

		/**
		 * @param string $label
		 * @return StylisedInput
		 */
		public function setLabel($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $value
		 * @return StylisedInput
		 */
		public function setValue($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * @return string
		 */
		public function __toString() {
			if ($this->type !== "textarea") {
				$s = <<<HTML
<div class="fi-wrap">
	<input type="%1\$s" name="%2\$s" id="%3\$s" value="%4\$s" pattern=".+"%6\$s />
	<label for="%3\$s">%5\$s</label>
</div>
HTML;
			} else {
				$s = <<<HTML
<div class="fi-wrap">
	<textarea name="%2\$s" id="%3\$s"%6\$s>%4\$s</textarea>
	<label for="%3\$s">%5\$s</label>
</div>
HTML;
			}


			return sprintf($s, $this->type, $this->name, $this->id, $this->value, $this->label, $this->isRequired ? " required=\"required\"": "");
		}
	}