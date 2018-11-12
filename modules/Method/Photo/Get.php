<?

	namespace Method\Photo;

	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;
	use Model\Photo;
	use Method\APIException;
	use Method\APIPublicMethod;
	use PDO;

	class Get extends APIPublicMethod {

		/** @var int|null */
		protected $ownerId = null;

		// OR

		/** @var int|null */
		protected $pointId = null;

		/** @var int */
		protected $count = 30;

		/** @var int */
		protected $offset = 0;

		/**
		 * Get constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Photo[]
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->ownerId === null && $this->pointId === null) {
				throw new APIException(ErrorCode::NO_PARAM, null, "ownerId and pointId is not specified");
			}

			$c = (int) $this->count;
			$o = (int) $this->offset;

			// SELECT * FROM `photo` lEFT JOIN `user` ON `user`.`userId` = `photo`.`photoId` WHERE `user`.`userId` = '1' ORDER BY `photo`.`photoId` DESC
			if ($this->ownerId) {
				$sql = <<<SQL
SELECT
	*
FROM
	`photo`
WHERE
	`type` = :type AND
	`ownerId` = :id
ORDER BY
	`photoId` DESC
LIMIT $o, $c
SQL;
				$type = Photo::TYPE_PROFILE;
				$id = $this->ownerId;
			} else {
				$sql = <<<SQL
SELECT
	*
FROM
	`photo`,
	`pointPhoto`
WHERE
	`photo`.`type` = :type AND
	`pointPhoto`.`pointId` = :id AND
	`photo`.`photoId` = `pointPhoto`.`photoId`
ORDER BY
	`pointPhoto`.`id`ASC
LIMIT $o, $c
SQL;
				$type = Photo::TYPE_POINT;
				$id = $this->pointId;
			}

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":id" => $id, ":type" => $type]);

			/** @var Photo[] $items */
			$items = parseItems($stmt->fetchAll(PDO::FETCH_ASSOC), "\\Model\\Photo");

			$res = [
				"items" => $items
			];

			if ($this->pointId) {
				$userIds = [];

				foreach ($items as $photo) {
					$userIds[] = $photo->getOwnerId();
				}

				$res["users"] = $main->perform(new \Method\User\GetByIds((new Params)->set("userIds", join(",", $userIds))));
			}


			return $res;
		}
	}