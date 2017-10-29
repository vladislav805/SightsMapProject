<?

	namespace Method\Photo;

	use Model\Photo;
	use APIException;
	use APIPrivateMethod;
	use Params;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

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
		 * @param \IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(\IController $main, DatabaseConnection $db) {
			/** @var Photo $photo */
			$photo = $main->perform(new GetById((new Params())->set("photoId", $this->photoId)));

			assertOwner($main, $photo->getOwnerId(), ERROR_ACCESS_DENIED);

			$sql = sprintf("DELETE FROM `photo` WHERE `photoId` = '%d'", $this->photoId);

			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameThumbnail());
			unlink("./userdata/" . $photo->getPath() . "/" . $photo->getNameOriginal());

			return (boolean) $db->query($sql, DatabaseResultType::AFFECTED_ROWS);
		}
	}