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
				throw new APIException(ErrorCode::SESSION_NOT_FOUND, null, "Access denied: authKey is required for perform this method");
			}

			if ($main->getSession() && $main->getSession()->getUserId() > ADMIN_ID_LIMIT) {
				throw new APIException(ErrorCode::ACCESS_DENIED, null, "Access denied");
			}

			return $this->resolve($main);
		}

	}