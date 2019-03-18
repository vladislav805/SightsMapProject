<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;
	use Model\User;
	use ObjectController\UserController;

	/**
	 * Получение информации о пользователях из БД по их идентификаторам
	 * @package Method\User
	 */
	class GetByIds extends APIPublicMethod {

		/** @var int[]|string[] */
		protected $userIds;

		/** @var string[] */
		protected $extra = [];

		/**
		 * @param IController $main
		 * @return User[]
		 */
		public function resolve(IController $main) {
			return (new UserController($main))->getByIds($this->userIds, $this->extra);
		}
	}