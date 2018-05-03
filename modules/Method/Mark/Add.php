<?php

	namespace Method\Mark;

	use Method\APIException;
	use Model\IController;
	use Model\Mark;
	use Method\APIModeratorMethod;

	/**
	 * Добавление новой метки
	 * @package Method\Mark
	 */
	class Add extends APIModeratorMethod {

		/** @var string */
		protected $title;

		/** @var int */
		protected $color;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return Mark
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if (!inRange($this->color, 0x0, 0xffffff)) {
				throw new APIException(ERROR_INVALID_COLOR);
			}

			$stmt = $main->makeRequest("INSERT INTO `mark` (`title`, `color`) VALUES (?, ?)");
			$stmt->execute([$this->title, $this->color]);

			return $main->perform(new GetById(["markId" => $main->getDatabaseProvider()->lastInsertId()]));
		}
	}