<?php

	use Model\Params;

	require_once "utils.php";

	class PointTest extends BasicTest {

		private static $lat = -10.5;
		private static $lng = -10.5;

		private static $latIncorrect = -190;
		private static $lngIncorrect = -190;

		/**
		 * @return array
		 */
		public function testAddPoint() {
			$this->setSession($this->testAccountAuthKey);
			$args["point"] = $this->perform(new \Method\Point\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$lat,
				"lng" => self::$lng
			]));
			$this->assertGreaterThan(0, $args["point"]->getId());
			return $args;
		}

		/**
		 * @depends testAddPoint
		 * @param $args
		 * @expectedException \Method\APIException
		 * @return array
		 */
		public function testAddPointIncorrect10($args) {
			$this->setSession($this->testAccountAuthKey);
			$args["incorrectPoints"][] = $this->perform(new \Method\Point\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$latIncorrect,
				"lng" => self::$lng
			]));
			return $args;
		}

		/**
		 * @depends testAddPointIncorrect10
		 * @param $args
		 * @expectedException \Method\APIException
		 * @return array
		 */
		public function testAddPointIncorrect01($args) {
			$this->setSession($this->testAccountAuthKey);
			$args["incorrectPoints"][] = $this->perform(new \Method\Point\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$lat,
				"lng" => self::$lngIncorrect
			]));
			return $args;
		}

		/**
		 * @depends testAddPointIncorrect01
		 * @param $args
		 * @expectedException \Method\APIException
		 * @return array
		 */
		public function testAddPointIncorrectBoth($args) {
			$this->setSession($this->testAccountAuthKey);
			$args["incorrectPoints"][] = $this->perform(new \Method\Point\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$latIncorrect,
				"lng" => self::$lngIncorrect
			]));
			return $args;
		}

		/**
		 * @depends
		 * @param $args
		 * @return array
		 */
		/*public function testRemoveAll($args) {

			return func_get_args();
		}*/



	}
