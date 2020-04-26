<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\Comment;
	use Model\IController;
	use Model\User;
	use ObjectController\CommentController;
	use ObjectController\UserController;

	class Report extends APIPrivateMethod {

		/** @var int */
		protected $commentId;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {
			if (!$this->commentId) {
				throw new APIException(ErrorCode::NO_PARAM);
			}

			/** @var User $currentUser */
			$currentUser = $main->getUser();

			/** @var Comment $comment */
			$comment = (new CommentController($main))->getById($this->commentId);

			/** @var User $author */
			$author = (new UserController($main))->getById($comment->getUserId());

			$str = <<<CODE
<p>Пользователь <a href='//sights.velu.ga/user/%s'>%s %s</a> пожаловался на комментарий пользователя <a href='//sights.velu.ga/user/%s'>%s %s</a></p>
<blockquote style='border-left: 2px solid black; background:#a0a0a0;padding:8px'>%s</blockquote>
<div style="text-align: center"><a class="ButtonLink" href='//sights.velu.ga/sight/%d'>Открыть комментарии</a></div>
CODE;


			$text = sprintf(
				$str,
				$currentUser->getLogin(),
				$currentUser->getFirstName(),
				$currentUser->getLastName(),
				$author->getLogin(),
				$author->getFirstName(),
				$author->getLastName(),
				$comment->getText(),
				$comment->getSightId()
			);

			send_mail_to_admin("Жалоба на комментарий", $text);

			return true;
		}
	}