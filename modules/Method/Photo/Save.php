<?php

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;

	class Save extends APIPrivateMethod {

		/** @var string */
		protected $hash;

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


			$key = $this->hash;

			if (!$redis->exists($key)) {
				throw new APIException(ErrorCode::PHOTO_UPLOAD_HASH_EXPIRED, null, "Upload result hash has expired. Try again later.");
			}

			$data = json_decode($redis->get($key));

			if ($data === null || $data === false) {
				throw new APIException(ErrorCode::PHOTO_UPLOAD_DATA_BROKEN, null, "Unknown error occurred");
			}

			$results = [];

			$sql = <<<SQL
INSERT INTO
	`photo` (`date`, `ownerId`, `path`, `type`, `photo200`, `photoMax`, `latitude`, `longitude`, `width`, `height`, `prevailColors`)
VALUES
	(UNIX_TIMESTAMP(NOW()), :uid, :path, :type, :src200, :srcMax, :lat, :lng, :w, :h, :clr)
SQL;

			$stmt = $main->makeRequest($sql);

			foreach ($data as $file) {
				$stmt->execute([
					":uid" => $file->uid,
					":path" => $file->path,
					":type" => $file->type,
					":src200" => $file->src200,
					":srcMax" => $file->srcMax,
					":w" => $file->w,
					":h" => $file->h,
					":lat" => $file->gLat,
					":lng" => $file->gLng,
					":clr" => $file->colors
				]);

				$results[] = (int) $main->getDatabaseProvider()->lastInsertId();
			}

			$redis->del($key);

			return $main->perform(new GetByIds((new Params())->set("photoIds", $results)));
		}

	}