<?php

	namespace Method\User;

	use Method\APIException;
	use Method\Authorize\CreateSession;
	use Method\Authorize\KillAllSessions;
	use Model\IController;
	use Method\APIPrivateMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class ChangePassword extends APIPrivateMethod {

		/** @var string */
		protected $oldPassword;

		/** @var string */
		protected $newPassword;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return CreateSession
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$oldHash = $main->perform(new GetPasswordHash(["password" => $this->oldPassword]));
			$userId = $main->getSession()->getUserId();

			$sql = sprintf("SELECT COUNT(*) FROM `user` WHERE `userId` = '%d' AND `password` = '%s' LIMIT 1",$userId, $oldHash);
			$ok = $db->query($sql, DatabaseResultType::COUNT);

			if (!$ok) {
				throw new APIException(ERROR_INCORRECT_LOGIN_PASSWORD);
			}

			$newHash = $main->perform(new GetPasswordHash(["password" => $this->newPassword]));

			$sql = sprintf("UPDATE `user` SET `password` = '%s' WHERE `userId` = '%d' LIMIT 1", $newHash, $userId);
			$success = $db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			if (!$success) {
				throw new APIException(ERROR_UNKNOWN_ERROR);
			}

			$main->perform(new KillAllSessions([]));
			$main->perform($session = new CreateSession(["userId" => $userId, "access" => $main->getSession()->getAccess()]));

			return $session;
		}
	}