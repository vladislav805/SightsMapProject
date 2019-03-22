<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Mark;
	use ObjectController\MarkController;

	/**
	 * Редактирование информации о метке
	 * @package Method\Mark
	 */
	class Edit extends APIModeratorMethod {

		/** @var int */
		protected $markId;

		/** @var string */
		protected $title;

		/** @var int */
		protected $color;

		/**
		 * @param IController $main
		 * @return Mark
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$ctl = new MarkController($main);

			$mark = $ctl->getById($this->markId);

			if (!$mark) {
				throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
			}

			$mark->setTitle($this->title)->setColor($this->color);

			return $ctl->edit($mark);
		}
	}