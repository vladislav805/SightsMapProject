<?php

	namespace Method\Photo;

	use InvalidArgumentException;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use RuntimeException;
	use tools\ExifGPSPoint;
	use tools\ImageText;
	use tools\SingleImage;

	class FetchPhoto extends APIPrivateMethod {

		const LENGTH_CHUNK_FILENAME = 5;

		/** @var string */
		protected $hash;

		/** @var array|null */
		protected $files;

		/** @var int */
		protected $qi;

		public function __construct($request) {
			parent::__construct($request);

			$file = $_FILES["files"];
			if (!is_string($file["name"])) {
				$this->files = reArrayFiles($file);
			} else {
				$this->files = [$file];
			}

			$this->qi = (int) $this->qi;
		}

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {

			if (!$this->hash) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Hash is empty");
			}

			$redis = $main->getRedis();


			$key = "p" . $this->hash;

			if (!$redis->exists($key)) {
				throw new APIException(ErrorCode::PHOTO_UPLOAD_HASH_EXPIRED, null, "Upload hash has expired. Try again later.");
			}

			$data = json_decode($redis->get($key));

			if (!$data) {
				throw new APIException(ErrorCode::PHOTO_UPLOAD_DATA_BROKEN, null, "Unknown error occurred");
			}

			if ($data->uniqId !== $this->qi) {
				throw new APIException(ErrorCode::ACCESS_DENIED, null, "Unique id invalid");
			}

			if (!sizeOf($this->files)) {
				throw new APIException(ErrorCode::PHOTO_NOT_SPECIFIED, null, "No photo file");
			}

			$results = [];

			foreach ($this->files as $file) {
				$results[] = $this->handlePhoto($file, $data);
			}

			$redis->del($key);

			$sum = array_sum(array_map(function($p) { return $p["w"] * $p["h"]; }, $results));

			$hashResult = hash("sha256", rand($sum / 2, 2 * $sum) * $main->getUser()->getId());

			$redis->set($hashResult, json_encode($results, JSON_UNESCAPED_UNICODE));

			return [
				"hash" => $hashResult
			];
		}

		/**
		 * @param array $file
		 * @param \stdClass $prefs
		 * @return mixed
		 * @throws APIException
		 */
		private function handlePhoto($file, $prefs) {
			try {
				$img = new SingleImage($file["tmp_name"]);

				if (
					$prefs->type === Photo::TYPE_PROFILE && min($img->getWidth(), $img->getHeight()) < UPLOAD_PHOTO_PROFILE_MIN_SIZE ||
					$prefs->type === Photo::TYPE_SIGHT && min($img->getWidth(), $img->getHeight()) < UPLOAD_PHOTO_SIGHT_MIN_SIZE
				) {
					$str = sprintf("Resolution of photo will be greater %d (profile) or %d (sight)", UPLOAD_PHOTO_PROFILE_MIN_SIZE, UPLOAD_PHOTO_SIGHT_MIN_SIZE);
					throw new APIException(ErrorCode::UPLOAD_INVALID_RESOLUTION, null, $str);
				}

				$name = mb_substr(hash("sha256", time() . $file["tmp_name"]), 0, self::LENGTH_CHUNK_FILENAME * 4);

				$hashes = str_split_unicode($name, self::LENGTH_CHUNK_FILENAME);

				$name = array_pop($hashes);
				$path = $hashes[0];
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

				$colors = [];

				try {
					$cls = new \tools\PrevailColors($file["tmp_name"]);

					$res = $cls->getColors(3, true, true, 24);

					$colors = array_keys($res);
				} catch (RuntimeException $e) {

				}

				mkdir($fullPath, 0755, true);

				$img->resizeToMaxSizeSide(PHOTO_MAX_SIDE_SIZE);

				if (((int) $prefs->type) === Photo::TYPE_SIGHT) {
					$text = (new ImageText(PHOTO_WATERMARK_OFFSET_X, $img->getHeight() - PHOTO_WATERMARK_OFFSET_Y, DOMAIN_MAIN))
						->setColor(0xffffff)
						->setFontFace(PHOTO_WATERMARK_FONT_FACE)
						->setFontSize(PHOTO_WATERMARK_FONT_SIZE);

					$dimen = $text->getDimens();

					$img->drawRect(0, $img->getHeight() - PHOTO_WATERMARK_OFFSET_Y * 2 - 16, $dimen["width"] + PHOTO_WATERMARK_OFFSET_X * 2, $img->getHeight(), 0xdd000000);

					$img->drawText($text);
				}

				$iw = $img->getWidth();
				$ih = $img->getHeight();

				$img->save($fullPath . $pB, IMAGETYPE_JPEG, PHOTO_MAX_COMPRESSION);

				$img->resizeToMaxSizeSide(PHOTO_THUMB_SIDE_SIZE);
				$img->save($fullPath . $pS, IMAGETYPE_JPEG, PHOTO_THUMB_COMPRESSION);

				return [
					"uid" => $prefs->userId,
					"path" => $path,
					"type" => $prefs->type,
					"w" => $iw,
					"h" => $ih,
					"src200" => $pS,
					"srcMax" => $pB,
					"lat" => $gLat,
					"lng" => $gLng,
					"colors" => join(PHOTO_PREVAIL_COLOR_DELIMITER, $colors)
				];
			} catch (RuntimeException $e) {
				throw new APIException(ErrorCode::UNKNOWN_ERROR, [
					"exception" => $e,
					"message" => $e->getMessage(),
					"trace" => $e->getTraceAsString()
				], "Unknown error occurred");
			}
		}
	}