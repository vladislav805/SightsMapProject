<?
	/**
	 * Created by vlad805.
	 */

	namespace UI;

	class StylisedSelect implements UIElement {

		/** @var string */
		private $name;

		/** @var string */
		private $id;

		/** @var array[] */
		private $items;

		/** @var string */
		private $label;

		/**
		 * StylisedSelect constructor.
		 * @param string $name
		 * @param string $label
		 * @param array[] $values
		 * @param string|null $id
		 */
		public function __construct($name, $label, $values = [], $id = null) {
			$this->name = $name;
			$this->label = $label;
			$this->items = $values;
			$this->id = $id === null ? md5($name) : $id;
		}

		/**
		 * @param string $label
		 * @return StylisedSelect
		 */
		public function setLabel($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param array[] $items
		 * @return StylisedSelect
		 */
		public function setItems($items) {
			$this->items = $items;
			return $this;
		}

		/**
		 * @return string
		 */
		public function __toString() {
			$res = ["<div class=\"fi-wrap\"><select name=\"%1\$s\" required id=\"%2\$s\">"];

			foreach ($this->items as $item) {
				/** @noinspection HtmlUnknownAttribute */
				$res[] = sprintf("<option value=\"%1\$s\" %3\$s%4\$s>%2\$s</option>", htmlSpecialChars($item["value"]), htmlSpecialChars($item["label"]), $item["selected"] ? " selected" : "", $item["inselectable"] ? " disabled hidden" : "");
			}

			$res[] = "</select><label for=\"%2\$s\">%3\$s</label></div>";


			return sprintf(join("", $res), $this->name, $this->id, $this->label);
		}
	}