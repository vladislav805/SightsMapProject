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
	 * Получение сессии по authKey
	 * @package Method\Authorize
	 */
	class GetSession extends APIPublicMethod {

		/** @var string */
		protected $authKey;

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = $main->makeRequest("
SELECT
	*
FROM
	`authorize`,
	`user` LEFT JOIN `photo` ON `user`.`userId` = `photo`.`ownerId` AND `photo`.`type` = 2 AND `photo`.`photoId` >= ALL (
		SELECT `photo`.`photoId` FROM `photo` WHERE `photo`.`ownerId` = `user`.`userId` AND `photo`.`type` = 2
	)
WHERE
	`authKey` = ? AND
	`user`.`userId` = `authorize`.`userId`
");
			$sql->execute([$this->authKey]);

			$session = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$session) {
				throw new APIException(ErrorCode::SESSION_NOT_FOUND);
			}

			return [
				new Session($session),
				new User($session)
			];
		}
	}