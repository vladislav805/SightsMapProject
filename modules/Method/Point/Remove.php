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

			$res = $db->query(sprintf("DELETE FROM `point` WHERE `ownerId` = '%d' AND `pointId` = '%d' LIMIT 1", $ownerId, $this->pointId), DatabaseResultType::AFFECTED_ROWS);

			return (boolean) $res;
		}
	}