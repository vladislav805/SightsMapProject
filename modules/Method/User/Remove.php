<?php

	namespace Method\User;

	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Удаление пользователя. Убрано намеренно.
	 * @package Method\User
	 */
	class Remove extends APIPrivateMethod {

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			return false;
		}
	}