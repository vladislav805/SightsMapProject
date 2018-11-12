<?

	namespace Method\Photo;

	use Method\ErrorCode;
	use function Method\Event\sendEvent;
	use Model\IController;
	use Model\Photo;
	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\Params;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $photoId;

		/**
		 * Remove constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			/** @var Photo $photo */
			$photo = $main->perform(new GetById((new Params())->set("photoId", $this->photoId)));

			if (!$photo) {
				throw new APIException(ErrorCode::PHOTO_NOT_FOUND);
			}

			$sql = <<<SQL
DELETE FROM
	`photo`
WHERE `photoId` IN (
	SELECT
		`photoId`
	FROM
		`authorize`, `user`
	WHERE
		`photo`.`photoId` = :photoId AND
		(
			(`user`.`userId` = `authorize`.`userId` AND (`user`.`status` = 'ADMIN' OR `user`.`status` = 'MODERATOR')) OR
			`photo`.`ownerId` = `authorize`.`userId`
		) AND 
    	`photo`.`ownerId` = `authorize`.`userId` AND
    	`authorize`.`authKey` = :authKey
)
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":photoId" => $this->photoId, ":authKey" => $main->getAuthKey()]);

			if (!$stmt->rowCount()) {
				return false;
			}

			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameThumbnail());
			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameOriginal());

			// TODO: send event on remove and reference to place
			/*if ($photo->getOwnerId() != $main->getSession()->getUserId()) {
				sendEvent($main, $photo->getOwnerId(), Event::EVENT_PHOTO_REMOVED, $photo->getId());
			}*/

			return true;
		}
	}