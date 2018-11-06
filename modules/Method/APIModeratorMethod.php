<?php

	namespace Method;

	use Model\IController;
	use Model\User;

	abstract class APIModeratorMethod extends APIPrivateMethod {

		/**
		 * APIModeratorMethod constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		protected function checkPermissions(IController $main) {
			parent::checkPermissions($main);

			$state = $main->getUser()->getStatus();

			if ($state !== User::STATE_MODERATOR && $state !== User::STATE_ADMIN) {
				throw new APIException(ErrorCode::ACCESS_FOR_METHOD_DENIED, null, "Not have permission to perform this action");
			}
		}

	}