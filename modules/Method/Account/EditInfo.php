<?php

	namespace Method\Account;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение информации о пользователе
	 * @package Method\Account
	 */
	class EditInfo extends APIPrivateMethod {

		use TCheckSexRange;

		/** @var string */
		protected $firstName;

		/** @var  string */
		protected $lastName;

		/** @var string */
		protected $sex;

		/** @var string */
		protected $bio;

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

			if (!$this->isSexInRange($this->sex)) {
				throw new APIException(ErrorCode::INVALID_SEX, null, "Sex value is invalid");
			}

			$sql = <<<SQL
UPDATE
	`user`, `authorize`
SET
	`firstName` = :fn,
	`lastName` = :ln,
	`sex` = :s,
    `bio` = :b,
	`cityId` = :ci
WHERE
	`user`.`userId` = `authorize`.`userId` AND `authorize`.`authKey` = :ak
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([
				":fn" => $this->firstName,
				":ln" => $this->lastName,
				":s" => $this->sex,
				":b" => $this->bio,
				":ak" => $main->getAuthKey(),
				":ci" => $this->cityId > 0 ? $this->cityId : null
			]);
			return (boolean) $stmt->rowCount();
		}
	}