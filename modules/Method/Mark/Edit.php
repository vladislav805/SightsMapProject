<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Mark;

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
			if (!inRange($this->color, 0x0, 0xffffff)) {
				throw new APIException(ErrorCode::INVALID_COLOR, null, "Invalid color code is specified");
			}

			$stmt = $main->makeRequest("UPDATE `mark` SET `title` = ?, `color` = ? WHERE `markId` = ?");
			$stmt->execute([$this->title, $this->color, $this->markId]);
			if (!$stmt->rowCount()) {
				throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
			}

			return $main->perform(new GetById(["markId" => $this->markId]));
		}
	}