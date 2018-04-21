<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIModeratorMethod {

		/** @var int */
		protected $markId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("DELETE FROM `mark` WHERE `markId` = '%d'", $this->markId);

			if (!$db->query($sql, DatabaseResultType::AFFECTED_ROWS)) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return true;
		}
	}