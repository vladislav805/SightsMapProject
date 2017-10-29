<?php

use Method\User\Registration;
use PHPUnit\Framework\TestCase;
require_once "Params.php";

class RegistrationTest extends TestCase {

	public function testCreateAccount() {

		$p = new Params();

		$p->set("firstName", "Test");
		$p->set("lastName", "Test");
		$p->set("sex", "0");
		$p->set("login", "tester");
		$p->set("password", "123456");

		$res = new Registration($p);

		$this->assertEquals($res["result"], true);

	}

}
