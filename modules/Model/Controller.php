<?php

	namespace Model;

	use Method\APIException;
	use Method\APIMethod;

	abstract class Controller implements IController {

		/**
		 * @param APIMethod $method
		 * @throws APIException
		 */
		abstract function perform(APIMethod $method);

	}