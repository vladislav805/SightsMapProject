<?php

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;

	class GetUploadUri extends APIPrivateMethod {

		/** @var string */
		protected $type;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$allowedTypes = [
				"sight" => Photo::TYPE_SIGHT,
				"profile" => Photo::TYPE_PROFILE,
				"sight_suggest" => Photo::TYPE_SIGHT_SUGGESTED
			];

			if (!$this->type) {
				throw new APIException(ErrorCode::NO_PARAM, null, "Type not specified");
			}

			if (!isset($allowedTypes[$this->type])) {
				throw new APIException(ErrorCode::UNKNOWN_TARGET, null, "Unknown photo target");
			}

			$hash = hash("sha256", PASSWORD_SALT . (time() + rand(20, 40) * pow($main->getUser()->getId(), 2)));

			$redis = $main->getRedis();

			$uniqId = rand(0x1000, 0xffff);

			$redis->set("p" . $hash, json_encode([
				"type" => $allowedTypes[$this->type],
				"userId" => $main->getUser()->getId(),
				"uniqId" => $uniqId
			]), 1 * HOUR);

			return [
				"url" => sprintf("https://%s/api.php?method=photos.fetchPhoto&type=%d&hash=%s&qi=%d&authKey=%s", DOMAIN_MAIN, $allowedTypes[$this->type], $hash, $uniqId, $main->getAuthKey()),
				"hash" => $hash,
				"uniqId" => $uniqId
			];
		}
	}