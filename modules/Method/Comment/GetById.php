<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;
	use Model\Comment;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $commentId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Comment
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `comment` WHERE `commentId` = '%d'", $this->commentId);
			$item = $db->query($sql, DatabaseResultType::ITEM);

			if (!$item) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return new Comment($item);
		}
	}