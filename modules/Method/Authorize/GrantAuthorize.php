<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\Session;
	use Model\IController;
	use tools\DatabaseConnection;

	class GrantAuthorize extends APIPublicMethod {

		/** @var string */
		protected $repath;

		/** @var int */
		protected $access;

		/**
		 * GrantAuthorize constructor.
		 * @param array $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if ($this->access < 0) {
				throw new APIException(ERROR_ACCESS_DENIED);
			}

			return $main->perform(new CreateSession(["userId" => $main->getSession()->getUserId(), "access" => $this->access]));
		}
	}