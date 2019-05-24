<?php

	namespace Method\Comment;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\ListCount;
	use ObjectController\CommentController;

	/**
	 * Получение комментариев к месту
	 * @package Method\Comment
	 */
	class Get extends APIPublicMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $count = 50;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return ListCount
		 */
		public function resolve(IController $main) {
			return (new CommentController($main))->get($this->sightId, $this->count, $this->offset);
		}
	}