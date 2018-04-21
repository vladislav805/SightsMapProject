<?php

	namespace Method\User;

	use Model\IController;
	use Method\APIPrivateMethod;
	use tools\DatabaseConnection;

	/**
	 * Изменение информации о пользователе
	 * @package Method\User
	 */
	class EditInfo extends APIPrivateMethod {

		/** @var string */
		protected $firstName;

		/** @var  string */
		protected $lastName;

		/** @var int */
		protected $sex;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = <<<SQL
UPDATE
	`user`, `authorize`
SET
	`firstName` = :fn,
	`lastName` = :ln,
	`sex` = :s
WHERE
	`user`.`userId` = `authorize`.`userId` AND `authorize`.`authKey` = :ak
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":fn" => $this->firstName,
				":ln" => $this->lastName,
				":s" => $this->sex,
				":ak" => $main->getAuthKey()
			]);
			return (boolean) $stmt->rowCount();
		}
	}