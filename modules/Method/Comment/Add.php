<?php

	namespace Method\Comment;

	use Method\APIPrivateMethod;
	use Method\APIException;
	use Model\Comment;
	use Model\Event;
	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param DatabaseConnection $db
		 * @return Comment
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$point = $main->perform(new \Method\Point\GetById(["pointId" => $this->pointId]));

			if (!$point) {
				throw new APIException(ERROR_POINT_NOT_FOUND);
			}

			$userId = $main->getSession()->getUserId();
			$sql = sprintf("INSERT INTO `comment` (`pointId`, `date`, `userId`, `text`) VALUES ('%d', UNIX_TIMESTAMP(NOW()), '%d', '%s')", $point->getId(), $userId, $this->text);
			$commentId = $db->query($sql, DatabaseResultType::INSERTED_ID);

			if ($userId !== $point->getOwnerId()) {
				\Method\Event\sendEvent($main, $point->getOwnerId(), Event::EVENT_POINT_COMMENT_ADD, $point->getId());
			}

			return $main->perform(new GetById(["commentId" => $commentId]));
		}
	}