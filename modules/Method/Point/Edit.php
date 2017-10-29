<?php

	namespace Method\Point;

	use APIPrivateMethod;
	use APIException;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Edit extends APIPrivateMethod {

		protected $pointId;
		protected $title;
		protected $description = "";

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController      $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId || !$this->title) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$ownerId = $main->getSession()->getUserId();
// todo add request to verify
			$sql = sprintf("UPDATE `point` SET `title` = '%s', `description` = '%s', `dateUpdated` = UNIX_TIMESTAMP(NOW()), `isVerified` = '0' WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $this->title, $this->description, $ownerId, $this->pointId);

			$db->query($sql, DatabaseResultType::AFFECTED_ROWS);

			return $main->perform(new GetById(["pointId" => $this->pointId]));
		}
	}