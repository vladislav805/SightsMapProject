<?php

	interface IController {

		/**
		 * Perform some action
		 * @param APIMethod $method
		 * @return mixed
		 */
		public function perform(APIMethod $method);

		/**
		 * SQL query to database
		 * @param string $sql
		 * @param int $type
		 * @return mixed
		 */
		public function query(string $sql, int $type);

		/**
		 * @return Model\Session
		 */
		public function getSession();

		/**
		 * @return Model\User
		 */
		public function getUser();

	}