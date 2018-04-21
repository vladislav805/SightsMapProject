<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Session;
	use PDO;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param DatabaseConnection $db
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = $main->makeRequest("SELECT * FROM `authorize` WHERE `authKey` = ?");
			$sql->execute([$this->authKey]);

			$session = $sql->fetch(PDO::FETCH_ASSOC);

			if (!$session) {
				throw new APIException(ERROR_SESSION_NOT_FOUND);
			}

			return new Session($session);
		}
	}