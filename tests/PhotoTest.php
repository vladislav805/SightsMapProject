<?php

	use Model\Photo;

	require_once "utils.php";

	class PhotoTest extends BasicTest {

		private static $pointId = 247;
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
		 * @param $args
		 * @return array
		 */
		public function testRemove($args) {
			$this->setSession($this->testAccountAuthKey);
			$this->assertTrue($this->perform(new \Method\Photo\Remove(["photoId" => $args["photo"]->getId()])));

			/** @var Photo $p */
			$p = $args["photo"];

			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameOriginal());
			$this->assertFileNotExists("./userdata/" . $p->getPath() . $p->getNameThumbnail());

			return $args;
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
