<?

	namespace Pages;

	interface RibbonPage {

		/**
		 * @param mixed $data
		 * @return boolean
		 */
		public function hasRibbon($data);

		/**
		 * @param mixed $data
		 * @return string|null
		 */
		public function getRibbonImage($data);

		/**
		 * @param mixed $data
		 * @return string|array[]|null
		 */
		public function getRibbonContent($data);

	}