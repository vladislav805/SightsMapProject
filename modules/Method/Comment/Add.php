<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\UserController;

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
			$stmt = $main->makeRequest("INSERT INTO `comment` (`sightId`, `date`, `userId`, `text`) VALUES (:sid, UNIX_TIMESTAMP(NOW()), :uid, :txt)");
			$stmt->execute([":sid" => $this->sightId, ":uid" => $userId, ":txt" => $this->text]);

			$commentId = $main->getDatabaseProvider()->lastInsertId();

			return [
				"comment" => $main->perform(new GetById(["commentId" => $commentId])),
				"user" => $main->perform((new UserController($main))->getById($userId, ["photo", "city"]))
			];
		}
	}