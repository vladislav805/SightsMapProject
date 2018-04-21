<?php

	namespace Method\User;

	use Method\APIException;
	use Model\IController;
	use Model\User;
	use tools\DatabaseConnection;

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
		 * @param DatabaseConnection $db
		 * @return User
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$result = parent::resolve($main, $db);
			return isset($result[0]) ? $result[0] : null;
		}
	}