<?php

	use Model\Photo;

	require_once "utils.php";

	class PhotoTest extends BasicTest {

		private static $pointId = 470;
		private static $imagePath = "./tests/res/testImage.jpg";
		private static $imagePathSmall = "./tests/res/testImageSmall.jpg";

		/**
		 * @return array
		 */
		public function testUpload() {
			$this->setSession($this->testAccountAuthKey);

			$_FILES["file"] = [
				"error" => 0,
				"tmp_name" => self::$imagePath
			];

			/** @var Photo $p */
			$p = $args["photo"] = $this->perform(new \Method\Photo\Upload([
				"type" => Photo::TYPE_POINT
			]));

			$this->assertGreaterThan(0, $p->getId());
			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameOriginal());
			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameThumbnail());
			return $args;
		}

		/**
		 * @expectedException \Method\APIException
		 */
		public function testUploadWithoutFile() {
			$this->setSession($this->testAccountAuthKey);
			$_FILES["file"] = null;
			$this->perform(new \Method\Photo\Upload([
				"type" => Photo::TYPE_POINT
			]));
		}

		/**
		 * @expectedException \Method\APIException
		 */
		public function testUploadInvalidType() {
			$this->setSession($this->testAccountAuthKey);

			$_FILES["file"] = [
				"error" => 0,
				"tmp_name" => self::$imagePath
			];

			$this->perform(new \Method\Photo\Upload([
				"type" => 777
			]));
		}

		/**
		 * @expectedException \Method\APIException
		 */
		public function testUploadErrorWhileReceive() {
			$this->setSession($this->testAccountAuthKey);

			$_FILES["file"] = [
				"error" => UPLOAD_ERR_CANT_WRITE,
				"tmp_name" => self::$imagePath
			];

			$this->perform(new \Method\Photo\Upload([
				"type" => Photo::TYPE_POINT
			]));
		}

		/**
		 * @expectedException \Method\APIException
		 */
		public function testUploadInvalidSizes() {
			$this->setSession($this->testAccountAuthKey);

			$_FILES["file"] = [
				"error" => 0,
				"tmp_name" => self::$imagePathSmall
			];

			$this->perform(new \Method\Photo\Upload([
				"type" => Photo::TYPE_POINT
			]));
		}

		/**
		 * @depends testUpload
		 * @param array $args
		 * @return array
		 */
		public function testLinkPoint($args) {
			$this->setSession($this->testAccountAuthKey);

			/** @var Photo $photo */
			$photo = $args["photo"];

			$this->perform(new \Method\Point\SetPhotos(["pointId" => self::$pointId, "photoIds" => $photo->getId()]));

			$items = $this->perform(new \Method\Photo\Get(["pointId" => self::$pointId]));

			/** @var Photo $first */
			$first = $items[0];

			$this->assertEquals($first->getId(), $photo->getId());
			$this->assertEquals($first->getPath(), $photo->getPath());

			return $args;
		}


		/**
		 * @depends testUpload
		 * @param $args
		 * @return array
		 */
		public function testRemove($args) {
			/** @var Photo $p */
			$p = $args["photo"];

			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Photo\Remove(["photoId" => $p->getId()])));

			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameOriginal());
			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameThumbnail());

			return $args;
		}

		/**
		 * @depends testRemove
		 */
		public function testCheckLinkAfterRemove() {
			$items = $this->perform(new \Method\Photo\Get(["pointId" => self::$pointId]));

			$this->assertCount(0, $items);
		}

		/**
		 * @depends testRemove
		 * @expectedException \Method\APIException
		 */
		public function testRemoveNotExists() {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Photo\Remove(["photoId" => 99999999])));
		}

	}
