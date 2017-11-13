<?php

	use Method\APIException;
	use Method\APIMethod;
	use Model\IController;
	use tools\DatabaseConnection;

	abstract class APIPrivateMethod extends APIMethod {

		/**
		 * APIPrivateMethod constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function call(IController $main, DatabaseConnection $db) {
			if (!$main->getSession()) {
				throw new APIException(ERROR_SESSION_NOT_FOUND);
			}

			return $this->resolve($main, $db);
		}

	}