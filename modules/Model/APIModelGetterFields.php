<?

	namespace Model;

	trait APIModelGetterFields {

		public function __get($name) {
			if (property_exists($this, $name)) {
				return $this->{$name};
			} else {
				return null;
			}
		}

	}