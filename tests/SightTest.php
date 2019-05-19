<?php

	require_once "utils.php";

	class SightTest extends BasicTest {

		private static $lat = -10.5;
		private static $lng = -10.5;

		private static $latIncorrect = -190;
		private static $lngIncorrect = -190;

		private static $title = "Title";
		private static $description = "Desc";

		private static $titleEdited = "Title 2";
		private static $descriptionEdited = "Desc 2";

		private static $markIds = [1,1000,1001];

		/**
		 * @return array
		 */
		public function testAddSight() {
			$this->setSession($this->testAccountAuthKey);
			$args["sight"] = $this->perform(new \Method\Sight\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$lat,
				"lng" => self::$lng
			]));
			$this->assertGreaterThan(0, $args["sight"]->getId());
			return $args;
		}

		/**
		 * @depends testAddSight
		 * @expectedException \Method\APIException
		 */
		public function testAddSightIncorrect10() {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$latIncorrect,
				"lng" => self::$lng
			]));
		}

		/**
		 * @depends testAddSight
		 * @expectedException \Method\APIException
		 */
		public function testAddSightIncorrect01() {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\Add([
				"title" => self::$title,
				"description" => self::$description,
				"lat" => self::$lat,
				"lng" => self::$lngIncorrect
			]));
		}

		/**
		 * @depends testAddSight
		 * @expectedException \Method\APIException
		 */
		public function testAddSightIncorrectBoth() {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\Add([
				"title" => "Test",
				"description" => "Desc",
				"lat" => self::$latIncorrect,
				"lng" => self::$lngIncorrect
			]));
		}

		/**
		 * @depends testAddSight
		 * @param $args
		 * @return array
		 */
		public function testEdit($args) {
			$this->setSession($this->testAccountAuthKey);
			$p = $this->perform(new \Method\Sight\Edit([
				"sightId" => $args["sight"]->getId(),
				"title" => self::$titleEdited,
				"description" => self::$descriptionEdited
			]));

			$this->assertNotEquals($p->getTitle(), $args["sight"]->getTitle());
			$this->assertNotEquals($p->getDescription(), $args["sight"]->getDescription());
			return $args;
		}

		/**
		 * @depends testEdit
		 * @expectedException \Method\APIException
		 */
		public function testEditAlien() {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\Edit([
				"sightId" => 247,
				"title" => self::$titleEdited,
				"description" => self::$descriptionEdited
			]));
		}

		/**
		 * @depends testEdit
		 * @param $args
		 * @return array
		 */
		public function testMove($args) {
			$this->setSession($this->testAccountAuthKey);
			$p = $this->perform(new \Method\Sight\Move([
				"sightId" => $args["sight"]->getId(),
				"lat" => self::$lat + 1,
				"lng" => self::$lng + 1
			]));

			$this->assertNotEquals($p->getLat(), $args["sight"]->getLat());
			$this->assertNotEquals($p->getLng(), $args["sight"]->getLng());
			return $args;
		}

		/**
		 * @depends testMove
		 * @param $args
		 * @expectedException \Method\APIException
		 */
		public function testMoveInvalid($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\Move([
				"sightId" => $args["sight"]->getId(),
				"lat" => self::$latIncorrect,
				"lng" => self::$lngIncorrect
			]));
			return $args;
		}

		/**
		 * @depends testMove
		 * @param $args
		 * @return array
		 */
		public function testSetVisit($args) {
			$this->setSession($this->testAccountAuthKey);
			$p = $this->perform(new \Method\Sight\SetVisitState([
				"sightId" => $args["sight"]->getId(),
				"state" => "1"
			]));

			$this->assertEquals(1, $p->getVisitState());
			return $args;
		}

		/**
		 * @depends testSetVisit
		 * @expectedException \Method\APIException
		 * @param array $args
		 */
		public function testSetVisitInvalid($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Sight\SetVisitState([
				"sightId" => $args["sight"]->getId(),
				"state" => 100500
			]));
		}

		/**
		 * @depends testSetVisit
		 * @param $args
		 * @return array
		 */
		public function testSetMarks($args) {
			$this->setSession($this->testAccountAuthKey);
			$p = $this->perform(new \Method\Sight\SetMarks([
				"sightId" => $args["sight"]->getId(),
				"markIds" => join(",", self::$markIds)
			]));

			$this->assertEquals(join(",", $p->getMarkIds()), join(",", self::$markIds));

			return $args;
		}

		/**
		 * @depends testSetMarks
		 * @param $args
		 * @return array
		 */
		public function testRemove($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Sight\Remove(["sightId" => $args["sight"]->getId()])));
			return $args;
		}



	}
