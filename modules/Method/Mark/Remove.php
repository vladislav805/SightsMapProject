<?php

	namespace Method\Mark;

	use APIException;
	use APIPrivateMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $markId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param \IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			$sql = sprintf("DELETE FROM `mark` WHERE `markId` = '%d'", $this->markId);

			if (!$db->query($sql, DatabaseResultType::AFFECTED_ROWS)) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return true;
		}
	}