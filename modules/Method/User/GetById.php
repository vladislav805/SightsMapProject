<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\User;
	use ObjectController\UserController;

	/**
	 * Получение информации об одном пользователе. Обертка для GetByIds.
	 * @package Method\User
	 * @deprecated
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $userId;

		/**
		 * @param IController $main
		 * @return User
		 */
		public function resolve(IController $main) {
			return (new UserController($main))->getById($this->userId);
		}
	}