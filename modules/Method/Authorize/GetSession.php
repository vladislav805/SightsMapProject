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

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$sql = $main->makeRequest("SELECT * FROM `authorize`, `user` WHERE `authKey` = ? AND `user`.`userId` = `authorize`.`userId`");
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