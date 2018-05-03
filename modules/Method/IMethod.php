<?php

	namespace Method;

	use Model\IController;

	interface IMethod {

		/**
		 * Realization of some action
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main);

	};