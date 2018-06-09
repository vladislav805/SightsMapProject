<?php

	namespace Method\User;

	use Model\IController;
	use Method\APIPrivateMethod;

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

		/** @var int */
		protected $cityId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$sql = <<<SQL
UPDATE
	`user`, `authorize`
SET
	`firstName` = :fn,
	`lastName` = :ln,
	`sex` = :s,
	`cityId` = :ci
WHERE
	`user`.`userId` = `authorize`.`userId` AND `authorize`.`authKey` = :ak
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":fn" => $this->firstName,
				":ln" => $this->lastName,
				":s" => $this->sex,
				":ak" => $main->getAuthKey(),
				":ci" => $this->cityId > 0 ? $this->cityId : null
			]);
			return (boolean) $stmt->rowCount();
		}
	}