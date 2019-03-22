<?php

	namespace Method\Mark;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use ObjectController\MarkController;

	/**
	 * Получение всех существующих категорий меток
	 * @package Method\Mark
	 */
	class Get extends APIPublicMethod {

		/** @var boolean */
		protected $needCount = false;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			return (new MarkController($main))->get(null, 0, 0, [
				"needCount" => $this->needCount
			]);
		}
	}