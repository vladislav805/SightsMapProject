<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

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

			$userId = $main->getSession()->getUserId();
			$stmt = $main->makeRequest("INSERT INTO `comment` (`pointId`, `date`, `userId`, `text`) VALUES (:pid, UNIX_TIMESTAMP(NOW()), :uid, :txt)");
			$stmt->execute([":pid" => $this->sightId, ":uid" => $userId, ":txt" => $this->text]);

			$commentId = $main->getDatabaseProvider()->lastInsertId();

			// TODO: feed based by logs
			/*if ($userId !== $point->getOwnerId()) {
				\Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_COMMENT_ADD, $point->getId());
			}*/

			return [
				"comment" => $main->perform(new GetById(["commentId" => $commentId])),
				"user" => $main->perform(new \Method\User\GetById([]))
			];
		}
	}