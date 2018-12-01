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
		 * @return \Redis|\Credis_Client
		 */
		public function getRedis();

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