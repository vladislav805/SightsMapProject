<?php

	namespace Method\Mark;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Mark;
	use ObjectController\MarkController;

	/**
	 * Добавление новой метки
	 * @package Method\Mark
	 */
	class Add extends APIModeratorMethod {

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
			$mark = new Mark([
				"title" => $this->title,
				"color" => $this->color
			]);

			try {
				return (new MarkController($main))->add($mark);
			} catch (InvalidArgumentException $e) {
				throw new APIException(ErrorCode::INVALID_COLOR, null, "Invalid color code is specified");
			}
		}
	}