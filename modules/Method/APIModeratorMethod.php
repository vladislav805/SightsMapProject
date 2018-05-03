<?php

	namespace Method;

	use Model\IController;

	abstract class APIModeratorMethod extends APIPrivateMethod {

		/**
		 * APIModeratorMethod constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 * @override
		 */
		public function call(IController $main) {
			if (!$main->getSession()) {
				throw new APIException(ERROR_SESSION_NOT_FOUND);
			}

			if ($main->getSession() && $main->getSession()->getUserId() > ADMIN_ID_LIMIT) {
				throw new APIException(ERROR_ACCESS_DENIED);
			}

			return $this->resolve($main);
		}

	}