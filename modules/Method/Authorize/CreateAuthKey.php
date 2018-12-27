<?php

	namespace Method\Authorize;

	use Method\APIPublicMethod;
	use Model\IController;

	/**
	 * Создание токена
	 * @package Method\Authorize
	 */
	class CreateAuthKey extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/**
		 * @param IController $main
		 * @return string
		 */
		public function resolve(IController $main) {
			return hash("sha512", AUTH_KEY_SALT . $this->userId . time());
		}
	}