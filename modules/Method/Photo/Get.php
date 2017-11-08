<?

	namespace Method\Photo;

	use Model\Photo;
	use APIException;
	use APIPublicMethod;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param \IController $main
		 * @param DatabaseConnection $db
		 * @return Photo[]
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			if ($this->ownerId === null && $this->pointId === null) {
				throw new APIException(ERROR_NO_PARAM);
			}

			if ($this->ownerId) {
				$sql = sprintf("SELECT * FROM `photo` WHERE `ownerId` = '%d' AND `type` = '%d' ORDER BY `photoId` DESC LIMIT " . ((int) $this->offset) . "," . ((int) $this->count), $this->ownerId, Photo::TYPE_PROFILE);

			} else {
				$sql = sprintf("SELECT * FROM `photo` WHERE `photoId` IN (SELECT `photoId` FROM `pointPhoto` WHERE `pointId` = '%d') AND `type` = '%d' ORDER BY `photoId` DESC LIMIT " . ((int) $this->offset) . "," . ((int) $this->count), $this->pointId, Photo::TYPE_POINT);
			}

			$items = $db->query($sql, DatabaseResultType::ITEMS);

			return parseItems($items, "\\Model\\Photo");
		}
	}