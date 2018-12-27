<?php

	namespace Method\Event;

	use Method\APIPublicMethod;
	use Model\IController;

	class Send extends APIPublicMethod {

		/** @var int */
		protected $type;

		/** @var int */
		protected $toId;

		/** @var int */
		protected $subjectId;

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
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