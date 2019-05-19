<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIModeratorMethod;
	use Model\IController;
	use Model\Sight;

	/**
	 * Изменение верификации места
	 * @package Method\Sight
	 */
	class SetVerify extends APIModeratorMethod {

		/** @var int */
		protected $sightId;

		/** @var boolean */
		protected $state;

		/**
		 * @param IController $main
		 * @return mixed
		 * @throws APIException
		 */
		public function resolve(IController $main) {
			/** @var Sight $sight */
			$sight = $main->perform(new GetById(["sightId" => $this->sightId]));

			$stmt = $main->makeRequest("UPDATE `sight` SET `isVerified` = ? WHERE `sightId` = ? LIMIT 1");
			$stmt->execute([$this->state, $sight->getId()]);

			return (boolean) $stmt->rowCount();
		}
	}