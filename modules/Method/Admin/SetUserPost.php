<?php

	namespace Method\Admin;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\User;
	use PDO;

	/**
	 * @package Method\Admin
	 */
	class SetUserPost extends APIModeratorMethod {

		/** @var int */
		protected $userId;

		/** @var string */
		protected $status;

		/** @var array */
		private $STATUS = [
			"ADMIN" => 10,
			"MODERATOR" => 5,
			"USER" => 0
		];

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			if (!in_array($this->status, [User::STATE_USER, User::STATE_MODERATOR, User::STATE_ADMIN])) {
				throw new APIException(ErrorCode::INVALID_USER_STATE);
			}

			if (!$this->userId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "userId not specified");
			}

			$stmt = $main->makeRequest("SELECT `status` FROM `user` WHERE `userId` = :uid");
			$stmt->execute([":uid" => $this->userId]);

			if (!$stmt->rowCount()) {
				throw new APIException(ErrorCode::USER_NOT_FOUND);
			}
			list($status) = $stmt->fetch(PDO::FETCH_NUM);

			$currentUserStatus = $main->getUser()->getStatus();

			if ($this->compareStatus($currentUserStatus, $status) === -1) {
				throw new APIException(ErrorCode::ACCESS_DENIED, null, "You cannot downgrade a user who is higher in the post");
			}

			$stmt = $main->makeRequest("UPDATE `user` SET `status` = :st WHERE `userId` = :uid");
			$stmt->execute([":st" => $this->status, ":uid" => $this->userId]);

			return $stmt->rowCount() > 0;
		}

		/**
		 * Compare string names statuses
		 * @param string $a
		 * @param string $b
		 * @return int
		 */
		private function compareStatus($a, $b) {
			$a = $this->STATUS[$a];
			$b = $this->STATUS[$b];

			return $a === $b ? 0 : ($a < $b ? -1 : 1);
		}
	}