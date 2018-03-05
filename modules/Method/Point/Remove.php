<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;
	use Model\Params;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class Remove extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			/** @var Point $point */
			$point = $main->perform(new GetById((new Params)->set("pointId", $this->pointId)));

			assertOwner($main, $point->getOwnerId(), ERROR_ACCESS_DENIED);

			$ownerId = $point->getOwnerId();
// TODO: remove photos by split method and remove files
			$sql = [
				sprintf("DELETE FROM `point` WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $ownerId, $this->pointId),
				sprintf("DELETE FROM `photo` WHERE `photoId` IN (SELECT `photoId` FROM `pointPhoto` WHERE `pointId` = '%d')", $this->pointId),
				sprintf("DELETE FROM `pointPhoto` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `pointMark` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `pointVisit` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `comment` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `event` WHERE `type` IN (1, 7, 8, 9, 11) AND `subjectId` = '%d'", $this->pointId)
			];

			$res = 0;

			foreach ($sql as $sqlItem) {
				$res += $db->query($sqlItem, DatabaseResultType::AFFECTED_ROWS);
			}

			return (boolean) $res;
		}
	}