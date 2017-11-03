<?php

	namespace Method\Point;

	use APIException;
	use APIPrivateMethod;
	use IController;
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

			$ownerId = $main->getSession()->getUserId();
// TODO: remove photos by split method and remove files
			$sql = [
				sprintf("DELETE FROM `point` WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $ownerId, $this->pointId),
				sprintf("DELETE FROM `photo` WHERE `photoId` IN (SELECT `photoId` FROM  `pointPhoto` WHERE `pointId` = '%d')", $this->pointId),
				sprintf("DELETE FROM `pointPhoto` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `pointMark` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `pointVisit` WHERE `pointId` = '%d' LIMIT 1", $this->pointId),
				sprintf("DELETE FROM `comment` WHERE `pointId` = '%d' LIMIT 1", $this->pointId)
			];

			$res = 0;

			foreach ($sql as $sqlItem) {
				$res += $db->query($sqlItem, DatabaseResultType::AFFECTED_ROWS);
			}

			return (boolean) $res;
		}
	}