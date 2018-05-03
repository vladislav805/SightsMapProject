<?php

	namespace Method\User;

	use Method\APIPrivateMethod;
	use Model\IController;

	/**
	 * Удаление пользователя. Убрано намеренно.
	 * @package Method\User
	 */
	class Remove extends APIPrivateMethod {

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			return false;
		}
	}