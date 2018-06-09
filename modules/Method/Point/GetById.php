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
			$sql = <<<SQL
SELECT
	`point`.*,
	`city`.`name`,
	`photo`.`ownerId` AS `photoOwnerId`,
	`photo`.`photoId`,
	`photo`.`date` AS `photoDate`,
	`photo`.`path`,
	`photo`.`photo200`,
	`photo`.`photoMax`
FROM
	`point`
		LEFT JOIN `city` ON `city`.`cityId` = `point`.`cityId`
		LEFT JOIN `pointPhoto` ON `pointPhoto`.`pointId` = `point`.`pointId`
		LEFT JOIN `photo` ON `pointPhoto`.`photoId` = `photo`.`photoId`
WHERE
	`point`.`pointId` = :pointId 
GROUP BY `point`.`pointId`
LIMIT 1
SQL;

			$stmt = $main->makeRequest($sql);
			$stmt->execute([":pointId" => $this->pointId]);
			$item = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ERROR_POINT_NOT_FOUND);
			}

			$item = new Point($item);

			($user = $main->getUser()) && $item->setAccessByCurrentUser($user);

			return $item;
		}
	}