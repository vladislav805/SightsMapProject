<?php

	use Model\Params;

	require_once "../autoload.php";

	class BasicTest extends PHPUnit\Framework\TestCase {

		/**
		 * @var MainController
		 */
		private $main;


		public function setUp() {
			$this->main = new MainController;
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