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


			$rows = $db->query(sprintf("
SELECT
	COUNT(`res`.`id`) AS `count`,
	`res`.`state`
FROM (
	SELECT
		*
	FROM
		`pointVisit`
	WHERE
		`pointId` = '%d'
	) `res`
GROUP BY
	`state`", $point->getId()), DatabaseResultType::ITEMS);

			$d = [0, 0];

			foreach ($rows as $row) {
				$d[$row["state"] - 1] = (int) $row["count"];
			}

			list($visited, $desired) = $d;

			return [
				"visited" => $visited,
				"desired" => $desired
			];
		}
	}