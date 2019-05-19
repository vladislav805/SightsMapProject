<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Изменение прикрепленных к месту категорий
	 * @package Method\Sight
	 */
	class SetMarks extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

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
			if (!$this->sightId) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId is not specified");
			}

			$sight = $main->perform(new GetById(["sightId" => $this->sightId]));

			assertOwner($main, $sight, ErrorCode::ACCESS_DENIED);

			$main->makeRequest("DELETE FROM `sightMark` WHERE `sightId` = ?")->execute([$this->sightId]);

			if (sizeOf($this->markIds)) {
				$ids = join(",", $this->markIds);
				$sql = <<<SQL
INSERT INTO
	`sightMark` (`sightId`, `markId`)
SELECT
	:sightId AS `sightId`,
	`markId`
FROM
	`mark`
WHERE
	`markId` IN ($ids)
SQL;

				$stmt = $main->makeRequest($sql);
				$stmt->execute([":sightId" => $this->sightId]);
			}

			//$main->getSession()->getUserId() > ADMIN_ID_LIMIT && sendEvent($main, MODERATOR_NOTIFY_USER_ID, Event::EVENT_POINT_NEW_UNVERIFIED, $this->pointId);

			return true;
		}
	}