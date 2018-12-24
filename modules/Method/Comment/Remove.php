<?php

	namespace Method\Comment;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $commentId;

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->commentId <= 0) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Invalid commentId is specified");
			}

			$sql = <<<SQL
DELETE FROM
	`comment`
WHERE `commentId` IN (
	SELECT
		`commentId`
	FROM
		`user`, `authorize`
	WHERE
		`comment`.`commentId` = :commentId AND
    	`comment`.`userId` = `user`.`userId` AND
    	`user`.`userId` = `authorize`.`userId` AND
    	`authorize`.`authKey` = :authKey
)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":commentId" => $this->commentId, ":authKey" => $main->getAuthKey()]);

			if (!$stmt->rowCount()) {
				throw new APIException(ErrorCode::COMMENT_NOT_FOUND, null, "Comment with specified commentId not found");
			}

			return true;
		}
	}