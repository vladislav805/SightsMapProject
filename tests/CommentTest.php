<?php

	use Model\ListCount;

	require_once "utils.php";

	class CommentTest extends BasicTest {

		private static $text = "Title";
		private static $pointId = 470;

		/**
		 * @return array
		 */
		public function testAdd() {
			$this->setSession($this->testAccountAuthKey);
			$args["comment"] = $this->perform(new \Method\Comment\Add([
				"pointId" => self::$pointId,
				"text" => self::$text
			]));
			$this->assertGreaterThan(0, $args["comment"]->getId());
			return $args;
		}

		/**
		 * @depends testAdd
		 * @expectedException \Method\APIException
		 */
		public function testAddForNotExistsComment() {
			$this->setSession($this->testAccountAuthKey);
			$this->perform(new \Method\Comment\Add([
				"pointId" => 99999999,
				"text" => self::$text
			]));
		}


		/**
		 * @depends testAdd
		 * @param $args
		 */
		public function testCheck($args) {
			$this->setSession($this->testAccountAuthKey);

			/** @var ListCount $data */
			$data = $this->perform(new \Method\Comment\Get([
				"pointId" => self::$pointId
			]));

			$this->assertCount(1, $data->getItems());
			$this->assertCount($data->getCount(), $data->getItems());

			$comment = $data->getItems()[0];

			$this->assertNotNull($comment);

			$this->assertEquals($args["comment"]->getId(), $comment->getId());

			return $args;
		}

		/**
		 * @depends testCheck
		 * @param $args
		 * @return array
		 */
		public function testRemove($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Comment\Remove(["commentId" => $args["comment"]->getId()])));
			return $args;
		}

		/**
		 * @depends testRemove
		 * @expectedException \Method\APIException
		 */
		public function testRemoveNotExists() {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Comment\Remove(["commentId" => 99999999])));
		}

	}
