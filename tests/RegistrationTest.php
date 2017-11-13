<?php

	use Model\Params;

	require_once "utils.php";

	class RegistrationTest extends BasicTest {

		private $userId;

		private $firstName = "testfn";
		private $lastName = "testln";
		private $login = "logintest";
		private $password = "aqdckvv";

		public function testCreateAccount() {

			$p = new Params([
				"firstName" => $this->firstName,
				"lastName" => $this->lastName,
				"sex" => "0",
				"login" => $this->login,
				"password" => $this->password
			]);

			$res = $this->perform(new Method\User\Registration($p));

			$this->assertTrue(true); //$res["result"]);
			$this->userId = $res["userId"];

			$p = new Params(["userId" => $this->userId]);

			/** @var \Model\User $res */
			$res = new \Method\User\GetById($p);

			$this->assertEquals($res->getId(), $this->userId);
			$this->assertEquals($res->getFirstName(), $this->firstName);
			$this->assertEquals($res->getLogin(), $this->login);
			$this->assertEquals($res->getSex(), "0");
		}

		public function testDestroyAccount() {

		}


	}
