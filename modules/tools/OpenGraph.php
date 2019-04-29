<?

	namespace tools;

	class OpenGraph {

		private $data;

		private $meta;

		const KEY_TYPE = "type";
		const KEY_TITLE = "title";
		const KEY_DESCRIPTION = "description";
		const KEY_IMAGE = "image";

		const TYPE_ARTICLE = "article";
		const TYPE_WEBSITE = "website";
		const TYPE_PROFILE = "profile";

		const KEY_PROFILE_FIRST_NAME = "profile:first_name";
		const KEY_PROFILE_LAST_NAME = "profile:last_name";
		const KEY_PROFILE_USERNAME = "profile:username";
		const KEY_PROFILE_GENDER = "profile:gender";

		const PROFILE_GENDER_FEMALE = "female";
		const PROFILE_GENDER_MALE = "male";

		const ARTICLE_PUBLISHED_TIME = "og:article:published_time";
		const ARTICLE_MODIFIED_TIME = "og:article:modified_time";
		const ARTICLE_AUTHOR = "og:article:author";

		public function __construct() {
			$get = http_build_query(get_http_query_wo_utm());

			$this->data = [
				"url" => "https://" . DOMAIN_MAIN . get_http_path() . ($get ? "?" . $get : "")
			];

			$this->meta = [];
		}

		/**
		 * @param string|array $key
		 * @param string|null $value
		 * @return $this
		 */
		public function set($key, $value = null) {
			if (is_array($key)) {
				foreach ($key as $k => $v) {
					$this->set($k, $v);
				}
				return $this;
			}
			$this->data[$key] = $value;
			return $this;
		}

		/**
		 * @param string $key
		 * @param string $value
		 */
		public function addMeta($key, $value) {
			$this->meta[$key] = $value;
		}

		public function __toString() {
			$html = [];
			foreach ($this->data as $key => $value) {
				$html[] = sprintf("<meta property=\"og:%s\" content=\"%s\" />\n", htmlSpecialChars($key), htmlSpecialChars($value));
			}

			foreach ($this->meta as $key => $value) {
				$html[] = sprintf("<meta name=\"%s\" content=\"%s\" />", htmlSpecialChars($key), htmlSpecialChars($value));
			}

			return join("", $html);
		}

	}