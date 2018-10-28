<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\Session;
	use Model\IController;

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
		 * @return Session
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->access < 0) {
				throw new APIException(ErrorCode::ACCESS_DENIED);
			}

			return $main->perform(new CreateSession(["userId" => $main->getSession()->getUserId(), "access" => $this->access]));
		}
	}