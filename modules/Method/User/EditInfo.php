<?php

	namespace Method\User;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

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

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {

			if (mb_strlen($this->firstName) < 2 || mb_strlen($this->lastName) < 2) {
				throw new APIException(ErrorCode::INCORRECT_NAMES, null, "Name and last name must be 2 or more symbols");
			}

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