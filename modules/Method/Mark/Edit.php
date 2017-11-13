<?php

	namespace Method\Mark;

	use APIModeratorMethod;
	use Model\IController;
	use Model\Mark;
	use Method\APIException;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Edit extends APIModeratorMethod {

		/** @var int */
		protected $markId;

		/** @var string */
		protected $title;

		/** @var int */
		protected $color;

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
			$sql = sprintf("UPDATE `mark` SET `title` = '%s', `color` = '%d' WHERE `markId` = '%d'", $this->title, $this->color, $this->markId);
			if (!$db->query($sql, DatabaseResultType::AFFECTED_ROWS)) {
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return $main->perform(new GetById(["markId" => $this->markId]));
		}
	}