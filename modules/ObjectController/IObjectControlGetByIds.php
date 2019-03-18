<?

	namespace ObjectController;

	interface IObjectControlGetByIds {

		/**
		 * @param int[] $id
		 * @param array|null $extra
		 * @return mixed
		 */
		public function getByIds($id, $extra = null);

	}