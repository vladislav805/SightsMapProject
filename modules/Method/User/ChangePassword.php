<?php

	namespace Method\User;

	use Method\APIException;
	use Method\Authorize\CreateSession;
	use Method\Authorize\KillAllSessions;
	use Method\ErrorCode;
	use Model\IController;
	use Method\APIPrivateMethod;
	use PDO;

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
		 * @return CreateSession
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$oldHash = $main->perform(new GetPasswordHash(["password" => $this->oldPassword]));
			$newHash = $main->perform(new GetPasswordHash(["password" => $this->newPassword]));

			$userId = $main->getSession()->getUserId();

			$args = [
				":id" => $main->getSession()->getUserId(),
				":op" => $oldHash
			];

			$stmt = $main->makeRequest("SELECT COUNT(*) AS `ok` FROM `user` WHERE `userId` = :id AND `password` = :op");
			$stmt->execute($args);

			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!((int) $row["ok"])) {
				throw new APIException(ErrorCode::INCORRECT_LOGIN_PASSWORD);
			}

			$args[":np"] = $newHash;

			$stmt = $main->makeRequest("UPDATE `user` SET `password` = :np WHERE `userId` = :id AND `password` = :op LIMIT 1");
			$stmt->execute($args);

			$success = (boolean) $stmt->rowCount();

			if (!$success) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR);
			}

			$main->perform(new KillAllSessions([]));
			$main->perform($session = new CreateSession(["userId" => $userId, "access" => $main->getSession()->getAccess()]));

			return $session;
		}
	}