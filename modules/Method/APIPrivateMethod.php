<?php

	namespace Method;

	use Model\IController;
	use Model\User;

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
		 * @throws APIException
		 */
		protected function checkPermissions(IController $main) {
			$session = $main->getSession();

			if (!$session) {
				throw new APIException(ErrorCode::SESSION_NOT_FOUND, null, "Access denied: authKey is required for perform this method");
			}

			$state = $main->getUser()->getStatus();
			if ($state === User::STATE_BANNED || $state === User::STATE_INACTIVE) {
				throw new APIException(ErrorCode::ACCESS_FOR_METHOD_DENIED, null, "Not have permission to perform this action");
			}
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 * @override
		 */
		public function call(IController $main) {
			$this->checkPermissions($main);
			return $this->resolve($main);
		}

	}