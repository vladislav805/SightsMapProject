<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\Comment;
	use Model\IController;
	use ObjectController\CommentController;
	use ObjectController\UserController;

	/**
	 * Добавление комментария
	 * @package Method\Comment
	 */
	class Add extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var string */
		protected $text;

		/**
		 * @param IController $main
		 * @return array
		 */
		public function resolve(IController $main) {
			if ($this->sightId <= 0) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND);
			}

			if (!mb_strlen($this->text)) {
				throw new APIException(ErrorCode::EMPTY_TEXT);
			}

			$ctl = new CommentController($main);

			$comment = new Comment([
				"sightId" => $this->sightId,
				"userId" => $main->getUser()->getId(),
				"text" => $this->text
			]);

			$ctl->add($comment);

			return [
				"comment" => $comment,
				"user" => (new UserController($main))->getById($comment->getUserId(), ["photo"])
			];
		}
	}