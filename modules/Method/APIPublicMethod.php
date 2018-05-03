<?php

	namespace Method;

	use Model\IController;

	abstract class APIPublicMethod extends APIMethod {

		/**
		 * APIPublicMethod constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function call(IController $main) {
			return $this->resolve($main);
		}

	}