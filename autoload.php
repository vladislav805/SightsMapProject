<?php

	strpos(getcwd(), "tests") && chdir("../");

	spl_autoload_register(function($class) {
		$file = dirname(__FILE__) . "/modules/" . str_replace("\\", "/", $class) . ".php";

		if (!file_exists($file)) {
			throw new RuntimeException("MySplAutoload: not found file by path '" . $file . "'");
		}

		/** @noinspection PhpIncludeInspection */
		require_once $file;
	});


	require_once "config.php";
	require_once "functions.php";
	require_once "modules/Model/IController.php";
	require_once "modules/Model/Controller.php";
	require_once "modules/MainController.php";
	require_once "modules/Utils/CityTree.php";

