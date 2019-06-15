<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Model\IController;
	use PDO;

	/**
	 * Получение списка возможных причин для того, чтобы пожаловаться на достопримечательность
	 * @package Method\Sight
	 */
	class GetReportReasons extends APIPrivateMethod {

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `reportReasonSight` ORDER BY `reasonId`");
			$stmt->execute();

			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return [
				"text" => "Пожалуйста, обозначьте причину по которой Вы хотите пожаловаться на достопримечательность. Жалоба будет рассмотрена вручную модератором или администратором в течение некоторого времени.",
				"items" => parseItems($res, "\\Model\\ReportReason")
			];
		}
	}