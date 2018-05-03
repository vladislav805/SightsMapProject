<?php

	namespace Method\Mark;

	use Model\IController;
	use Model\Mark;
	use Method\APIException;
	use Method\APIPublicMethod;
	use PDO;

	/**
	 * Получение информации о метке по ее идентификатору
	 * @package Method\Mark
	 */
	class GetById extends APIPublicMethod {

		/** @var int */
		protected $markId;

		public function __construct($request) {
			parent::__construct($request);
		}

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
				throw new APIException(ERROR_MARK_NOT_FOUND);
			}

			return new Mark($item);
		}
	}