<?php

	namespace Method\Comment;

	use APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;
	use Model\Comment;
	use MainController;
	use APIException;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $commentId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param \IController|MainController $main
		 * @param DatabaseConnection $db
		 * @return Comment
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `comment` WHERE `commentId` = '%d'", $this->commentId);
			$item = $db->query($sql, DatabaseResultType::ITEM);

			if (!$item) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return new Comment($item);
		}
	}