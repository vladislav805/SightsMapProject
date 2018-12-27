<?php

	namespace Method\Event;

	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Сброс счетчиков новых событий у текущего пользователя
	 * @package Method\Event
	 */
	class ReadAll extends APIPrivateMethod {

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
UPDATE
	`event`, `user`, `authorize`
SET
	`isNew` = 0
WHERE
	`event`.`isNew` <> 0 AND
	`event`.`ownerUserId` = `user`.`userId` AND 
	`user`.`userId` = `authorize`.`userId` AND 
	`authorize`.`authKey` = :authKey
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":authKey" => $main->getAuthKey()]);
			return (boolean) $stmt->rowCount();
		}
	}