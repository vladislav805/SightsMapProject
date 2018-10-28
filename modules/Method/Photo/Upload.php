<?php

	namespace Method\Photo;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;
	use Model\Photo;
	use RuntimeException;
	use tools\ExifGPSPoint;
	use tools\ImageText;
	use tools\SingleImage;

	class Upload extends APIPrivateMethod {

		const LENGTH_CHUNK_FILENAME = 12;

		/** @var array */
		protected $file;

		/** @var int */
		protected $type;

		public function __construct($request) {
			parent::__construct($request);
			$this->file = isset($_FILES["file"]) ? $_FILES["file"] : null;
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->file || !inRange($this->type, Photo::TYPE_POINT, Photo::TYPE_PROFILE)) {
				throw new APIException(ErrorCode::NO_PARAM, null, "File not specified or type not TYPE_POINT/TYPE_PROFILE");
			}

			if (!$main->perform(new CheckFlood(new Params))) {
				throw new APIException(ErrorCode::FLOOD_CONTROL, null, "Flood blocked");
			}

			if ($this->file["error"]) {
				throw new APIException(ErrorCode::UPLOAD_FAILURE, ["code" => $this->file["error"]], "Error while receiving file");
			}

			try {
				$img = new SingleImage($this->file["tmp_name"]);

				if (
					$this->type === Photo::TYPE_PROFILE && min($img->getWidth(), $img->getHeight()) < UPLOAD_PHOTO_PROFILE_MIN_SIZE ||
					$this->type === Photo::TYPE_POINT && min($img->getWidth(), $img->getHeight()) < UPLOAD_PHOTO_POINT_MIN_SIZE
				) {
					throw new APIException(ErrorCode::UPLOAD_INVALID_RESOLUTION, null, "Resolution of photo will be greater 720 (profile) or 1200 (sight)");
				}

				$name = mb_substr(hash("sha256", time() . $this->file["tmp_name"]), 0, self::LENGTH_CHUNK_FILENAME * 4);

				$hashes = str_split_unicode($name, self::LENGTH_CHUNK_FILENAME);

				$name = array_pop($hashes);
				$path = join("/", $hashes);
				$fullPath = "./userdata/" . $path . "/";
				$pB = $name . ".b.jpg";
				$pS = $name . ".s.jpg";

				$gLat = null;
				$gLng = null;

				try {
					$gps = new ExifGPSPoint($img->readExif());

					if ($gps->hasGPSMark()) {
						$gLat = $gps->getLatitude();
						$gLng = $gps->getLongitude();
					}
				} catch (InvalidArgumentException $e) {

				}

				mkdir($fullPath, 0755, true);

				$img->resizeToMaxSizeSide(PHOTO_WATERMARK_MAX_SIDE_SIZE);

				if (((int) $this->type) === Photo::TYPE_POINT) {
					$text = (new ImageText(PHOTO_WATERMARK_OFFSET_X, $img->getHeight() - PHOTO_WATERMARK_OFFSET_Y, DOMAIN))
						->setColor(0xffffff)
						->setFontFace(PHOTO_WATERMARK_FONT_FACE)
						->setFontSize(PHOTO_WATERMARK_FONT_SIZE);

					$dimen = $text->getDimens();

					$img->drawRect(0, $img->getHeight() - PHOTO_WATERMARK_OFFSET_Y * 2 - 16, $dimen["width"] + PHOTO_WATERMARK_OFFSET_X * 2, $img->getHeight(), 0xdd000000);

					$img->drawText($text);
				}

				$img->save($fullPath . $pB, IMAGETYPE_JPEG, PHOTO_WATERMARK_MAX_COMPRESSION);

				$img->resizeToMaxSizeSide(PHOTO_WATERMARK_THUMB_SIDE_SIZE);
				$img->save($fullPath . $pS, IMAGETYPE_JPEG, PHOTO_WATERMARK_THUMB_COMPRESSION);

				$ownerId = $main->getSession()->getUserId();

				if (!$gLat && $gLng) {
					$gLat = null;
					$gLng = null;
				}

				$sql = <<<SQL
INSERT INTO
	`photo` (`date`, `ownerId`, `path`, `type`, `photo200`, `photoMax`, `latitude`, `longitude`)
VALUES
	(UNIX_TIMESTAMP(NOW()), :uid, :path, :type, :src200, :srcMax, :lat, :lng)
SQL;

				$stmt = $main->makeRequest($sql);
				$stmt->execute([
					":uid" => $ownerId,
					":path" => $path,
					":type" => $this->type,
					":src200" => $pS,
					":srcMax" => $pB,
					":lat" => $gLat,
					":lng" => $gLng
				]);

				$id = $main->getDatabaseProvider()->lastInsertId();

				return $main->perform(new GetById((new Params())->set("photoId", $id)));
			} catch (RuntimeException $e) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, $e, "Unknown error occurred");
			}
		}
	}