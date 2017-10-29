<?php

	namespace Method\Event;

	use APIPublicMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Send extends APIPublicMethod {

		/** @var int */
		protected $type;

		/** @var int */
		protected $toId;

		/** @var int */
		protected $subjectId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return int
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			if (!$main->getSession()) {
				return 0;
			}

			$sql = sprintf("INSERT INTO `event` (`date`, `type`, `ownerUserId`, `actionUserId`, `subjectId`) VALUES (UNIX_TIMESTAMP(NOW()), '%d', '%d', '%d', '%d')", $this->type, $this->toId, $main->getSession()->getUserId(), $this->subjectId);

			return $db->query($sql, DatabaseResultType::INSERTED_ID);
		}
	}