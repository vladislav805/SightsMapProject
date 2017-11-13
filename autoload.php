<?php

	strpos(getcwd(), "tests") && chdir("../");

	spl_autoload_register(function($class) {

		if (strpos($class, "Model") === false && strpos($class, "Method") === false && strpos($class, "tools") === false) {
			return;
		}

		/** @noinspection PhpIncludeInspection */
		require_once "modules/" . str_replace("\\", "/", $class) . ".php";
	});


	require_once "config.php";
	require_once "functions.php";
	require_once "modules/MainController.php";

