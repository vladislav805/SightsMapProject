<?
	/**
	 * Created by vlad805.
	 */

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
		private $isRequired;

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
			return sprintf("<div class=\"fi-wrap\">
			<input type=\"%s\" name=\"%s\" id=\"%s\" %s value=\"%s\" pattern=\".+\" required=\"required\" />
			<label for=\"%3\$s\">%s</label>
		</div>", $this->type, $this->name, $this->id, $this->isRequired ? " required=\"required\"" : "", $this->value, $this->label);
		}
	}