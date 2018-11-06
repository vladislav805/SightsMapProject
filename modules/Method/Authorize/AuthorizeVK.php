<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Method\User\GetPasswordHash;
	use Model\Session;
	use Model\User;
	use PDO;

	/**
	 * Авторизация через ВКонтакте
	 * @package Method\Authorize
	 */
	class AuthorizeVK extends APIPublicMethod {

		/** @var int */
		protected $vkId;

		/**
		 * @param IController $main
		 * @return boolean|Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `user` WHERE `vkId` = :vkId LIMIT 1");
			$stmt->execute([":vkId" => $this->vkId]);

			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$result) {
				return false;
			}

			$user = new User($result);

			if (!$user->getId()) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, null, "Unknown error: userId is null");
			}

			return $main->perform(new CreateSession(["userId" => $user->getId(), "access" => -1]));
		}
	}