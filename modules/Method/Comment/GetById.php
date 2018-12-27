<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\Comment;
	use Model\IController;
	use PDO;

	/**
	 * Получение комментария по его идентификатору
	 * @package Method\Comment
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $commentId;

		/**
		 * @param IController $main
		 * @return Comment
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = $main->makeRequest("SELECT * FROM `comment` WHERE `commentId` = ?");
			$sql->execute([$this->commentId]);
			$item = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ErrorCode::COMMENT_NOT_FOUND, null, "Comment with specified id not found");
			}

			return new Comment($item);
		}
	}