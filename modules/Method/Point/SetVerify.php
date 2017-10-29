<?php

	namespace Method\Point;

	use APIException;
	use APIModeratorMethod;
	use IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class SetVerify extends APIModeratorMethod {

		/** @var int */
		protected $pointId;

		/** @var boolean */
		protected $state;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			$sql = sprintf("UPDATE `point` SET `isVerified` = '%d' WHERE `pointId` = '%d' LIMIT 1", $this->state, $this->pointId);
			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}