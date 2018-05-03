<?php

	namespace Method;

	use Model\IController;

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
		 * @return mixed
		 * @throws APIException
		 */
		public function call(IController $main) {
			if (!$main->getSession()) {
				throw new APIException(ERROR_SESSION_NOT_FOUND);
			}

			return $this->resolve($main, $db);
		}

	}