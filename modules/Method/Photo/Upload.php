<?php

	namespace Method\Photo;

	use Method\APIException;
	use APIPrivateMethod;
	use Model\IController;
	use Model\Params;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;
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
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->file || !$this->type) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if (!$main->perform(new CheckFlood(new Params))) {
				throw new APIException(ERROR_FLOOD_CONTROL);
			}

			if ($this->file["error"]) {
				throw new APIException(ERROR_UPLOAD_FAILURE, ["code" => $this->file["error"]]);
			}

			$img = new SingleImage($this->file["tmp_name"]);
			$name = mb_substr(hash("sha256", time() . $this->file["tmp_name"]), 0, self::LENGTH_CHUNK_FILENAME * 4);

			$hashes = str_split_unicode($name, self::LENGTH_CHUNK_FILENAME);

			$name = array_pop($hashes);
			$path = join("/", $hashes);
			$fullPath = "./userdata/" . $path . "/";
			$pB = $name . ".b.jpg";
			$pS = $name . ".s.jpg";

			mkdir($fullPath, 0755, true);

			$img->resizeToMaxSizeSide(1400);
			$img->save($fullPath . $pB, IMAGETYPE_JPEG, 98);

			$img->resizeToMaxSizeSide(200);
			$img->save($fullPath . $pS, IMAGETYPE_JPEG, 50);

			$ownerId = $main->getSession()->getUserId();

			$sql = sprintf("INSERT INTO `photo` (`date`, `ownerId`, `path`, `type`, `photo200`, `photoMax`) VALUES (UNIX_TIMESTAMP(NOW()), '%d', '%s', '%d', '%s', '%s')", $ownerId, $path, $this->type, $pS, $pB);
			$id = $db->query($sql, DatabaseResultType::INSERTED_ID);

			return $main->perform(new GetById((new Params())->set("photoId", $id)));
		}
	}