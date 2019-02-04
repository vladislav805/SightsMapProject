<?php

	namespace Method\Admin;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Method\User\GetById;
	use Model\IController;

	/**
	 * @package Method\Admin
	 */
	class SetBan extends APIModeratorMethod {

		/** @var int */
		protected $userId;

		/** @var int */
		protected $reason;

		/** @var int */
		protected $comment;

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			if (!$this->userId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "userId not specified");
			}

			$user = $main->perform(new GetById(["userId" => $this->userId]));

			if (!$user) {
				throw new APIException(ErrorCode::USER_NOT_FOUND);
			}

			// unban
			if (!$this->reason) {
				$sqls = [
					"DELETE FROM `ban` WHERE `userId` = :uid",
					"UPDATE `user` SET `status` = 'USER' WHERE `userId` = :uid"
				];

				foreach ($sqls as $sql) {
					$stmt = $main->makeRequest($sql);
					$stmt->execute([":uid" => $this->userId]);
				}

				return true;
			}

			// ban

			$stmt = $main->makeRequest("UPDATE `user` SET `status` = 'BANNED' WHERE `userId` = :uid");
			$stmt->execute([":uid" => $this->userId]);

			$stmt = $main->makeRequest("INSERT INTO `ban` (`userId`, `reason`, `comment`) VALUES (?, ?, ?)");
			$stmt->execute([$this->userId, $this->reason, $this->comment]);

			return $main->getDatabaseProvider()->lastInsertId() !== null;
		}
	}