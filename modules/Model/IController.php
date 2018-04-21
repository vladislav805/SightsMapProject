<?php

	namespace Model;

	use Method\APIMethod;
	use PDO;
	use PDOStatement;

	interface IController {

		/**
		 * Выполнение метода API
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method);

		/**
		 * SQL query to database
		 * @param string $sql
		 * @param int $type
		 * @deprecated
		 * @return mixed
		 */
		public function query(string $sql, int $type);

		/**
		 * Запрос к БД через PDO
		 * @param string $sql
		 * @return PDOStatement
		 */
		public function makeRequest(string $sql);

		/**
		 * Возвращает PDO
		 * @return PDO
		 */
		public function getDatabaseProvider();

		/**
		 * Проверка на то, есть ли у пользователя авторизация
		 * @return boolean
		 */
		public function isAuthorized();

		/**
		 * Возвращает токен, который передал пользователь
		 * @return string
		 */
		public function getAuthKey();

		/**
		 * @return Session
		 */
		public function getSession();

		/**
		 * @return User
		 */
		public function getUser();

	}