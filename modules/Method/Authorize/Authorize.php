<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Method\User\GetById;
	use Model\Session;

	/**
	 * Авторизация
	 * @package Method\Authorize
	 */
	class Authorize extends APIPublicMethod {

		protected $repath = null;
		protected $access = 0;

		/** @var string */
		protected $login;

		/** @var string */
		protected $password;

		/**
		 * Authorize constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if (!$this->repath && (!$this->login || !$this->password) || $this->repath && !$this->access) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Login or password is empty");
			}

			$session = !$this->repath
				? $this->login($main, $this->login, $this->password)
				: $this->grant($main, $this->repath, $this->access);

			$json = $session->jsonSerialize();
			$json["user"] = $main->perform(new GetById(["userIds" => $session->getUserId()]));
			return $json;
		}

		/**
		 * @param IController $main
		 * @param string $login
		 * @param string $password
		 * @return Session
		 */
		private function login(IController $main, $login, $password) {
			return $main->perform(new DirectAuthorize(["login" => $login, "password" => $password]));
		}

		/**
		 * @param IController $main
		 * @param string $repath
		 * @param string $access
		 * @return Session
		 * @deprecated
		 */
		private function grant(IController $main, $repath, $access) {
			return $main->perform(new GrantAuthorize(["repath" => $repath, "access" => $access]));
		}
	}