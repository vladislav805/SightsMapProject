<?php

	namespace Method\Internal;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Получение всех существующих категорий меток
	 * @package Method\Mark
	 */
	class GetPage extends APIPublicMethod {

		/** @var string */
		protected $id;

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			$id = basename($this->id);
			$path = ROOT_PROJECT . "/pages/json/" . $id . ".json";

			if (!file_exists($path)) {
				throw new APIException(ErrorCode::PAGE_NOT_FOUND);
			}

			return json_decode(file_get_contents($path));
		}
	}