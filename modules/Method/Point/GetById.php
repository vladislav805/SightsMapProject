<?php

	namespace Method\Point;

	use Model\Point;
	use Model\IController;
	use Method\APIPublicMethod;
	use Method\APIException;
	use tools\DatabaseConnection;
	use tools\DatabaseResultType;

	class GetById extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController			  $main
		 * @param DatabaseConnection $db
		 * @return Point
		 * @throws APIException
		 */
		public function resolve(IController $main, DatabaseConnection $db) {
			$sql = sprintf("SELECT * FROM `point` WHERE `pointId` = '%d' LIMIT 1", $this->pointId);
			$item = $db->query($sql, DatabaseResultType::ITEM);

			if (!$item) {
				throw new APIException(ERROR_POINT_NOT_FOUND);
			}

			$item = new Point($item);

			($user = $main->getUser()) && $item->setAccessByCurrentUser($user);

			return $item;
		}
	}