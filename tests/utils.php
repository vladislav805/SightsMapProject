<?php

	use Model\Params;

	require_once "../autoload.php";

	class BasicTest extends PHPUnit\Framework\TestCase {

		/**
		 * @var MainController
		 */
		private $main;

		protected $firstName = "testfn";
		protected $lastName = "testln";
		protected $login = "logintest";
		protected $password = "aqdckvv";

		protected $testAccountAuthKey = "45734f49ff8b37edc10b4cd5e1821125298fe3bf0c58184f6efe05319f9df022dcc103104b4496f7e01a5222b828d34b3376de370ea691093bf05f8a185055ea";

		public function setUp() {
			$this->main = new MainController;
		}

		public function setSession($authKey) {
			return $this->main->setAuthKey($authKey);
		}

		protected function perform($method) {
			return $this->main->perform($method);
		}

		public function createParams($arr) {
			$p = new Params;

			foreach ($arr as $k => $v) {
				$p->set($k, $v);
			}

			return $p;
		}


	}