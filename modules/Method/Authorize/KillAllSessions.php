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

		/** @var boolean */
		protected $includeCurrent = false;

		/**
		 * @param IController $main
		 * @return int
		 */
		public function resolve(IController $main) {
			$current = $this->includeCurrent ? "" : "`authKey` <> :authKey AND";
			$sql = <<<SQL
DELETE FROM
	`authorize`
WHERE
	{$current}
	`userId` = (SELECT * FROM (SELECT `userId` FROM `authorize` `a` WHERE `a`.`authKey` = :authKey) `b`)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":authKey" => $main->getAuthKey()]);

			return $stmt->rowCount();
		}
	}