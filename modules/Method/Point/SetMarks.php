<?php

	namespace Method\Point;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use function Method\Event\sendEvent;
	use Model\Event;
	use Model\IController;
	use Model\Params;

	/**
	 * Изменение прикрепленных к месту категорий
	 * @package Method\Point
	 */
	class SetMarks extends APIPrivateMethod {

		/** @var int */
		protected $pointId;

		/** @var int[] */
		protected $markIds;

		/**
		 * GetMarks constructor.
		 * @param $request
		 */
		public function __construct($request) {
			parent::__construct($request);
			$this->markIds = array_map("intval", explode(",", $this->markIds));
		}

		/**
		 * @param IController $main
		 * @return boolean
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!$this->pointId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "pointId is not specified");
			}

			$point = $main->perform(new GetById((new Params())->set("pointId", $this->pointId)));

			assertOwner($main, $point, ErrorCode::ACCESS_DENIED);

			$main->makeRequest("DELETE FROM `pointMark` WHERE `pointId` = ?")->execute([$this->pointId]);

			if (sizeOf($this->markIds)) {
				$ids = join(",", $this->markIds);
				$sql = <<<SQL
INSERT INTO
	`pointMark` (`pointId`, `markId`)
SELECT
	:pointId AS `pointId`,
	`markId`
FROM
	`mark`
WHERE
	`markId` IN ($ids)
SQL;

				$stmt = $main->makeRequest($sql);
				$stmt->execute([":pointId" => $this->pointId]);
			}

			$main->getSession()->getUserId() > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return true;
		}
	}