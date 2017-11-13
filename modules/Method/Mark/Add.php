<?php

	namespace Method\Mark;

	use Method\APIException;
	use Model\IController;
	use Model\Mark;
	use APIModeratorMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Add extends APIModeratorMethod {

		protected $title;
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
			$sql = sprintf("INSERT INTO `mark` (`title`, `color`) VALUES ('%s', '%d')", $this->title, $this->color);
			$markId = $db->query($sql, DatabaseResultType::INSERTED_ID);

			return $main->perform(new GetById(["markId" => $markId]));
		}
	}