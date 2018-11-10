<?php

	namespace Method\Point;

	use Method\ErrorCode;
	use Model\Params;
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
				throw new APIException(ErrorCode::POINT_NOT_FOUND);
			}

			$item = new Point($item);

			if ($main->isAuthorized()) {
				$visited = $main->perform(new GetVisited(new Params));

				$item->setVisitState(isset($visited[$item->getId()]) ? $visited[$item->getId()] : 0);
			}

			$stmt = $main->makeRequest("SELECT `markId` FROM `pointMark` WHERE `pointId` = ?");
			$stmt->execute([$this->pointId]);
			$res = $stmt->fetchAll(PDO::FETCH_NUM);

			$res = array_map(function($item) {
				return (int) $item[0];
			}, $res);

			$item->setMarks($res);

			($user = $main->getUser()) && $item->setAccessByCurrentUser($user);

			return $item;
		}
	}