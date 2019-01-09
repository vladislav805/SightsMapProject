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

		/** @var int|null */
		protected $markId;

		/** @var int[]|null */
		protected $markIds;

		/**
		 * @param IController $main
		 * @return Mark|Mark[]
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->markId) {
				return $this->getOne($main, $this->markId);
			}

			if ($this->markIds) {
				return $this->getMultiple($main, $this->markIds);
			}

			throw new APIException(ErrorCode::NO_PARAM, null, "Arguments markId and markIds not specified");
		}

		/**
		 * @param IController $main
		 * @param int $markId
		 * @return Mark
		 */
		private function getOne($main, $markId) {
			$stmt = $main->makeRequest("SELECT * FROM `mark` WHERE `markId` = ?");
			$stmt->execute([$markId]);
			$item = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$item) {
				throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
			}

			return new Mark($item);
		}

		/**
		 * @param IController $main
		 * @param int[] $markIds
		 * @return Mark[]
		 */
		private function getMultiple($main, $markIds) {
			$stmt = $main->makeRequest("SELECT * FROM `mark` WHERE `markId` IN (" . join(",", $markIds) . ")");
			$stmt->execute();
			$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return parseItems($items, "\\Model\\Mark");
		}
	}