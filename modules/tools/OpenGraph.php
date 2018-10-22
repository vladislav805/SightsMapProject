<?

	namespace tools;

	class OpenGraph {

		private $data;

		const KEY_TYPE = "type";
		const KEY_TITLE = "title";
		const KEY_DESCRIPTION = "description";
		const KEY_IMAGE = "image";

		const KEY_PROFILE_FIRST_NAME = "profile:first_name";
		const KEY_PROFILE_LAST_NAME = "profile:last_name";
		const KEY_PROFILE_USERNAME = "profile:username";
		const KEY_PROFILE_GENDER = "profile:gender";

		const PROFILE_GENDER_FEMALE = "female";
		const PROFILE_GENDER_MALE = "male";


		public function __construct() {
			$this->data = [
				"url" => "https://" . DOMAIN . $_SERVER["REQUEST_URI"]
			];
		}

		public function set($key, $value) {
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->set($k, $v);
				}
				return $this;
			}
			$this->data[$key] = $value;
			return $this;
		}

		public function get() {
			$html = [];
			foreach ($this->data as $key => $value) {
				$html[] = sprintf("<meta property=\"og:%s\" content=\"%s\" />", htmlspecialchars($key), htmlspecialchars($value));
			}
			return array_values($html);
		}

	}