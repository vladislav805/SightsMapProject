<?php

	namespace Model;

	use Method\APIMethod;

	abstract class Controller implements IController {

		abstract function perform(APIMethod $method);

	}