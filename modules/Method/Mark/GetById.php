<?php

	namespace Method\Mark;

	use Method\APIException;
	use Method\APIPublicMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Mark;
	use ObjectController\MarkController;

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
			$ctl = new MarkController($main);

			if ($this->markId) {
				$mark = $ctl->getById($this->markId);

				if (!$mark) {
					throw new APIException(ErrorCode::MARK_NOT_FOUND, null, "Mark not found");
				}

				return $mark;
			}

			if ($this->markIds) {
				return $ctl->getByIds($this->markIds);
			}

			throw new APIException(ErrorCode::NO_PARAM, null, "Arguments markId and markIds not specified");
		}
	}
