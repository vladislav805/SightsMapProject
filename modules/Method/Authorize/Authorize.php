<?php

	namespace Method\Authorize;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use ObjectController\UserController;

	/**
	 * Авторизация
	 * @package Method\Authorize
	 */
	class Authorize extends APIPublicMethod {

		/** @var string */
		protected $login;

		/** @var string */
		protected $password;

		/**
		 * @param IController $main
		 * @return array
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if (!$this->login || !$this->password) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Login or password not specified");
			}

			$session = $main->perform(new DirectAuthorize(["login" => $this->login, "password" => $this->password]));

			$json = $session->jsonSerialize();
			if ($main instanceof \MainController) {
				$main->setAuthKey($session->getAuthKey());
			}

			$json["user"] = (new UserController($main))->getById($session->getUserId(), ["photo", "city", "extended"]);
			return $json;
		}

	}