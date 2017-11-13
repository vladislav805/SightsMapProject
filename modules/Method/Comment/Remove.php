<?php

	namespace Method\Comment;

	use APIPrivateMethod;
	use Method\APIException;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $commentId;

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
			$sql = sprintf("DELETE FROM `comment` WHERE `commentId` = '%d'", $this->commentId);

			if (!$db->query($sql, DatabaseResultType::AFFECTED_ROWS)) {
				throw new APIException(ERROR_COMMENT_NOT_FOUND);
			}

			return true;
		}
	}