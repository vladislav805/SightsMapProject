<?php

	namespace Method\Comment;

	use Method\APIPrivateMethod;
	use Method\APIException;
	use Model\Comment;
	use Model\IController;

	class Add extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var string */
		protected $text;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Comment
		 */
		public function resolve(IController $main) {
			$userId = $main->getSession()->getUserId();
			$stmt = $main->makeRequest("INSERT INTO `comment` (`pointId`, `date`, `userId`, `text`) VALUES (:pid, UNIX_TIMESTAMP(NOW()), :uid, :txt)");
			$stmt->execute([":pid" => $this->pointId, ":uid" => $userId, ":txt" => $this->text]);

			$commentId = $main->getDatabaseProvider()->lastInsertId();

			// TODO: feed based by logs
			/*if ($userId !== $point->getOwnerId()) {
				\Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_COMMENT_ADD, $point->getId());
			}*/

			return $main->perform(new GetById(["commentId" => $commentId]));
		}
	}