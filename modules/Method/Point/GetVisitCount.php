<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Model\IController;
	use Model\Params;
	use Model\Point;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetVisitCount extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @param DatabaseConnection $db
		 * @return mixed
		 * @throws \Method\APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			if (!$this->pointId) {
				throw new APIException(ERROR_NO_PARAM);
			}

			/** @var Point $point */
			$point = $main->perform(new GetById((new Params)->set("pointId", $this->pointId)));


			$visited = $db->query(sprintf("SELECT COUNT(*) FROM `pointVisit` WHERE `pointId` = '%d' AND `state` = 1", $point->getId()), DatabaseResultType::COUNT);
			$desired = $db->query(sprintf("SELECT COUNT(*) FROM `pointVisit` WHERE `pointId` = '%d' AND `state` = 2", $point->getId()), DatabaseResultType::COUNT);

			return [
				"visited" => $visited,
				"desired" => $desired
			];
		}
	}