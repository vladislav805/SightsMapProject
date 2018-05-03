<?php

	namespace Method\Point;

	use Model\Point;
	use Model\IController;
	use Method\APIPublicMethod;
	use Method\APIException;
	use PDO;

	/**
	 * Получение информации об одном месте по его идентификатору
	 * @package Method\Point
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $pointId;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Point
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `point` WHERE `pointId` = ? LIMIT 1");
			$stmt->execute([$this->pointId]);
			$item = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ERROR_POINT_NOT_FOUND);
			}

			$item = new Point($item);

			($user = $main->getUser()) && $item->setAccessByCurrentUser($user);

			return $item;
		}
	}