<?
	/** @noinspection PhpUnusedParameterInspection */

	namespace Pages;

	use Method\APIException;
	use Model\Mark;

	class MarkListPage extends BasePage {

		/**
		 * @param $action
		 * @return mixed
		 * @throws APIException
		 */
		protected function prepare($action) {
			return $this->mController->perform(new \Method\Mark\Get(["needCount" => true]));
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getBrowserTitle($data) {
			return "Метки мест";
		}

		/**
		 * @param mixed $data
		 * @return string
		 */
		public function getPageTitle($data) {
			return $this->getBrowserTitle($data);
		}

		public function getContent($data) {
			/** @var Mark[] $items */
			$items = $data->getItems();

			uasort($items, function(Mark $a, Mark $b) {
				return -($a->getCount() <=> $b->getCount());
			});
			require_once self::$ROOT_DOC_DIR . "marks.content.php";
		}

		/**
		 * @param Mark $item
		 */
		protected function item(Mark $item) {
			require self::$ROOT_DOC_DIR . "mark.item.php";
		}

	}