<?

	namespace Method\Photo;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Photo;
	use PDO;

	class Get extends APIPublicMethod {

		/** @var int|null */
		protected $ownerId = null;

		// OR

		/** @var int|null */
		protected $sightId = null;

		/** @var int */
		protected $count = 30;

		/** @var int */
		protected $offset = 0;

		/**
		 * @param IController $main
		 * @return Photo[]
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->ownerId === null && $this->sightId === null) {
				throw new APIException(ErrorCode::NO_PARAM, null, "ownerId and sightId is not specified");
			}

			$c = (int) $this->count;
			$o = (int) $this->offset;

			// SELECT * FROM `photo` lEFT JOIN `user` ON `user`.`userId` = `photo`.`photoId` WHERE `user`.`userId` = '1' ORDER BY `photo`.`photoId` DESC
			if ($this->ownerId) {
				$sql = "SELECT * FROM  `photo` WHERE `type` = :type AND `ownerId` = :id ORDER BY `photoId` DESC LIMIT $o, $c";
				$type = Photo::TYPE_PROFILE;
				$id = $this->ownerId;
			} else {
				$sql = "SELECT * FROM `photo`, `pointPhoto` WHERE `photo`.`type` = :type AND `pointPhoto`.`pointId` = :id AND `photo`.`photoId` = `pointPhoto`.`photoId` ORDER BY `pointPhoto`.`id`ASC LIMIT $o, $c";
				$type = Photo::TYPE_SIGHT;
				$id = $this->sightId;
			}

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":id" => $id, ":type" => $type]);

			/** @var Photo[] $items */
			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");

			$res = [
				"items" => $items
			];

			if ($this->sightId) {
				$userIds = [];

				foreach ($items as $photo) {
					$userIds[] = $photo->getOwnerId();
				}

				$res["users"] = $main->perform(new \Method\User\GetByIds(["userIds" => $userIds]));
			}

			return $res;
		}
	}