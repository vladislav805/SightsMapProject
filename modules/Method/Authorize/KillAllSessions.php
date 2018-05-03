<?php

	namespace Method\Authorize;

	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Завершение всех сессий, кроме текущей
	 * Возвращается количество сессий, которые были завершены
	 * @package Method\Authorize
	 */
	class KillAllSessions extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
DELETE FROM
	`authorize`
WHERE
	`authKey` <> :authKey AND
	`userId` IN (
		SELECT
			`userId`
		FROM
			`authorize`
		WHERE
			`authKey` = :authKey
	)
LIMIT 1
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([$main->getAuthKey()]);

			return $stmt->rowCount();
		}
	}