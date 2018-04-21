<?php

	namespace Method\Event;

	use Method\APIPublicMethod;
	use Model\IController;
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
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$main->getSession()) {
				return 0;
			}

			$sql = $main->makeRequest("INSERT INTO `event` (`date`, `type`, `ownerUserId`, `actionUserId`, `subjectId`) VALUES (UNIX_TIMESTAMP(NOW()), :type, :toId, :uid, :sid)");
			$sql->execute([
				":type" => $this->type,
				":toId" => $this->toId,
				":uid" => $main->getSession()->getUserId(),
				":sid" => $this->subjectId
			]);

			return $main->getDatabaseProvider()->lastInsertId();
		}
	}