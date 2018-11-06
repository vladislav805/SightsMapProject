<?php

	namespace Method\User;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\User;

	/**
	 * @package Method\User
	 */
	class Activate extends APIPublicMethod {

		/** @var string */
		protected $hash;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			$sql = <<<SQL
UPDATE
	`user` AS `u`,
	(
		SELECT
			*
		FROM
			`activate`
		WHERE
			`hash` = :h
	) AS `h`
SET
	`u`.`status` = :s
WHERE
	`u`.`userId` = `h`.`userId`
SQL;

			$stmt = $main->makeRequest($sql);

			$stmt->execute([
				":h" => $this->hash,
				":s" => User::STATE_USER
			]);

			$status = (boolean) $stmt->rowCount();

			if (!$status) {
				throw new APIException(ErrorCode::ACTIVATION_HASH_EXPIRED, null, "Hash has expired");
			}

			$stmt = $main->makeRequest("DELETE FROM `activate` WHERE `hash` = ?");
			$stmt->execute([$this->hash]);

			return $status;
		}
	}