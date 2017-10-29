<?php

	abstract class Controller implements IController {

		abstract function perform(APIMethod $method);

	}