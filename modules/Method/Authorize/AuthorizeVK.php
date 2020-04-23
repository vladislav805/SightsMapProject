<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
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
		 * @return array | Session | boolean
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

			/** @var Session $session */
			$session = $main->perform(new CreateSession(["userId" => $user->getId(), "access" => -1]));

			if (API_VERSION < 250) {
				return $session;
			} else {
				return array_merge($session->jsonSerialize(), [
					"user" => $user
				]);
			}
		}
	}