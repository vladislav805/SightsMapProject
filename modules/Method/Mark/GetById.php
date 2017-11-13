<?php

	namespace Method\Mark;

	use Model\IController;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;
	use Model\Mark;
	use Method\APIException;
	use Method\APIPublicMethod;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $markId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Mark
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `mark` WHERE `markId` = '%d'", $this->markId);
			$item = $db->query($sql, DatabaseResultType::ITEM);

			if (!$item) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return new Mark($item);
		}
	}