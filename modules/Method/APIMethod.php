<?php

	namespace Method;

	use Model\IController;

	abstract class APIMethod extends Method implements IMethod {

		/**
		 * @param IController $main
		 * @return mixed
		 */
		abstract function call(IController $main);

	}