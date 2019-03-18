<?

	namespace ObjectController;

	interface IObjectControlGetById {

		/**
		 * @param int $id
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getById($id, $extra = null);

	}