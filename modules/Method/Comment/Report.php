<?php

	namespace Method\Comment;

	use APIException;
	use APIPrivateMethod;
	use tools\DatabaseConnection;

	class Report extends APIPrivateMethod {

		/** @var int */
		protected $commentId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param \IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {


			return 0; //$main->perform(new GetById(["markId" => $this->markId]));
		}
	}