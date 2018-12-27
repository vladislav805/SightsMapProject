<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;

	/**
	 * Высчитывание хэш-суммы от пароля
	 * @package Method\User
	 */
	class GetPasswordHash extends APIPublicMethod {

		protected $password;

		/**
		 * @param IController $main
		 * @return string
		 */
		public function resolve(IController $main) {
			return hash("sha512", $this->password . PASSWORD_SALT);
		}
	}