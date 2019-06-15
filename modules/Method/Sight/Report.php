<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;

	/**
	 * Подача жалобы на достопримечательность
	 * @package Method\Sight
	 */
	class Report extends APIPrivateMethod {

		/** @var int */
		protected $sightId;

		/** @var int */
		protected $reasonId;

		/** @var string */
		protected $comment;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			if ($this->sightId <= 0 || $this->reasonId <= 0) {
				throw new APIException(ErrorCode::NO_PARAM, null, "sightId or/and reasonId not specified");
			}

			$stmt = $main->makeRequest("INSERT INTO `reportSight` (`userId`, `sightId`, `reasonId`, `date`, `comment`) VALUES (:uid, :sid, :rid, :dte, :cmt)");
			$stmt->execute([
				":uid" => $main->getUser()->getId(),
				":sid" => $this->sightId,
				":rid" => $this->reasonId,
				":dte" => time(),
				":cmt" => (string) $this->comment
			]);

			if ((int) $stmt->errorCode()) {
				throw new APIException(ErrorCode::SIGHT_NOT_FOUND, null, "Sight or reason not found");
			}

			$res = $main->getDatabaseProvider()->lastInsertId();

			return (int) $res;
		}
	}