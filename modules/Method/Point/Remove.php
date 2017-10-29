<?php

	namespace Method\Point;

	use APIException;
	use APIPrivateMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			$ownerId = $main->getSession()->getUserId();

			$sql = sprintf("DELETE FROM `point` WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $ownerId, $this->pointId);

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}