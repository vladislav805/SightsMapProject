<?php

	use Model\Params;

	require_once "utils.php";

	class RegistrationAndLoginUserTest extends BasicTest {

		public function testCreateAccount() {
			$res = $this->perform(new Method\User\Registration(new Params([
				"firstName" => $this->firstName,
				"lastName" => $this->lastName,
				"sex" => "0",
				"login" => $this->login,
				"password" => $this->password
			])));

			$this->assertTrue($res["result"]);

			return $res["userId"];

		}

		/**
		 * @param int $userId
		 * @depends testCreateAccount
		 */
		public function testGetUserById($userId) {
			$p = new Params(["userIds" => $userId]);

			/** @var \Model\User $res */
			$res = $this->perform(new \Method\User\GetById($p));

			$this->assertEquals($res->getId(), $userId);
			$this->assertEquals($res->getFirstName(), $this->firstName);
			$this->assertEquals($res->getLogin(), $this->login);
			$this->assertEquals($res->getSex(), "0");
		}

		/**
		 * @depends testCreateAccount
		 * @param int $userId
		 * @return array
		 */
		public function testAuthorize($userId) {
			$auth = $this->perform(new \Method\Authorize\Authorize([
				"login" => $this->login,
				"password" => $this->password
			]));

			$this->assertEquals($auth["userId"], $userId);
			$this->assertEquals($auth["user"]->getLogin(), $this->login);

			return ["userId" => $userId, "authKey" => $auth["authKey"], "user" => $auth["user"]];
		}

		/**
		 * @depends testCreateAccount
		 */
		public function testIsFreeLogin() {
			$this->assertFalse($this->perform(new Method\User\IsFreeLogin(new Params(["login" => $this->login]))));
		}

		/**
		 * @depends testAuthorize
		 * @param $data
		 */
		public function testRemoveAccount($data) {
			$this->setSession($data["authKey"]);
			$this->assertTrue($this->perform(new Method\User\Remove(new Params())));
		}



	}
