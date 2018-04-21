<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use PDO;
	use tools\DatabaseConnection;
	use Model\Comment;

	/**
	 * Получение комментария по его идентификатору
	 * @package Method\Comment
	 */
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
			$sql = $main->makeRequest("SELECT * FROM `comment` WHERE `commentId` = ?");
			$sql->execute([$this->commentId]);
			$item = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return new Comment($item);
		}
	}