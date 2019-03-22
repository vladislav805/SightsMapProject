<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\MarkController;

	/**
	 * Удаление метки
	 * @package Method\Mark
	 */
	class Remove extends APIModeratorMethod {

		/** @var int */
		protected $markId;

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$ctl = new MarkController($main);

			$mark = $ctl->getById($this->markId);

			if (!$mark) {
				throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
			}

			return $ctl->remove($mark);
		}
	}