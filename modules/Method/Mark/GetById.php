<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Mark;
	use PDO;

	/**
	 * Получение информации о метке по ее идентификатору
	 * @package Method\Mark
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $markId;

		/**
		 * @param IController $main
		 * @return Mark
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("SELECT * FROM `mark` WHERE `markId` = ?");
			$stmt->execute([$this->markId]);
			$item = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
			}

			return new Mark($item);
		}
	}