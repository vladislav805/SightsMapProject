<?php

	namespace Method\Authorize;

	use APIPublicMethod;
	use IController;
	use tools\DatabaseConnection;

	class CreateAuthKey extends APIPublicMethod {

		/** @var int */
		protected $userId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController			  $main
		 * @param DatabaseConnection $db
		 * @return string
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			return hash("sha512", AUTH_KEY_SALT . $this->userId . time());
		}
	}