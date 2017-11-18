<?php

	require_once "utils.php";

	class MarkTest extends BasicTest {

		private static $title = "Title";
		private static $color = 0x0ff00f;

		private static $titleEdited = "Title 2";
		private static $colorEdited = 0x00f000;

		/**
		 * @return array
		 */
		public function testAdd() {
			$this->setSession($this->testAccountAuthKey);
			$args["mark"] = $this->perform(new \Method\Mark\Add([
				"title" => "Test",
				"color" => self::$color
			]));
			$this->assertGreaterThan(0, $args["mark"]->getId());
			return $args;
		}

		/**
		 * @depends testAdd
		 * @param $args
		 * @return array
		 */
		public function testEdit($args) {
			$this->setSession($this->testAccountAuthKey);
			$p = $this->perform(new \Method\Mark\Edit([
				"markId" => $args["mark"]->getId(),
				"title" => self::$titleEdited,
				"color" => self::$colorEdited
			]));

			$this->assertEquals($p->getTitle(), self::$titleEdited);
			$this->assertEquals($p->getColor(), self::$colorEdited);
			return $args;
		}


		/**
		 * @depends testEdit
		 * @param $args
		 * @expectedException \Method\APIException
		 */
		public function testEditInvalid($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Mark\Edit([
				"markId" => $args["mark"]->getId(),
				"title" => self::$title,
				"color" => 0x1ffffff
			]));
			return $args;
		}

		/**
		 * @depends testEdit
		 * @param $args
		 * @return array
		 */
		public function testRemove($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Mark\Remove(["markId" => $args["mark"]->getId()])));
			return $args;
		}

	}
