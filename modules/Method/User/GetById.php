<?php

	namespace Method\User;

	use Model\IController;
	use Model\User;

	/**
	 * Получение информации об одном пользователе. Обертка для GetByIds.
	 * @package Method\User
	 */
	class GetById extends GetByIds {

		/** @var int */
		protected $userId;

		public function __construct($request) {
			parent::__construct($request);
			if ($this->userId) {
				$this->userIds = [$this->userId];
			}
		}

		/**
		 * @param IController $main
		 * @return User
		 */
		public function resolve(IController $main) {
			$result = parent::resolve($main);
			return $result[0] ?? null;
		}
	}