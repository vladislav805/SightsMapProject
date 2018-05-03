<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Model\IController;

	/**
	 * Удаление метки
	 * @package Method\Mark
	 */
	class Remove extends APIModeratorMethod {

		/** @var int */
		protected $markId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("DELETE FROM `mark` WHERE `markId` = ?");
			$stmt->execute([$this->markId]);

			if (!$stmt->rowCount()) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return true;
		}
	}