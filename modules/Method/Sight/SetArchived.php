<?php

	namespace Method\Sight;

	use Method\APIException;
	use Method\APIPrivateMethod;
	use Method\ErrorCode;
	use Model\IController;
	use Model\Params;
	use Model\Sight;

	/**
	 * Изменение актуальности места
	 * @package Method\Point
	 */
	class SetArchived extends APIPrivateMethod {

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
			$sight = $main->perform(new GetById((new Params)->set("sightId", $this->sightId)));

			assertOwner($main, $sight, ErrorCode::ACCESS_DENIED);

			$stmt = $main->makeRequest("UPDATE `point` SET `isArchived` = ? WHERE `pointId` = ? LIMIT 1");
			$stmt->execute([$this->state, $sight->getId()]);

			return (boolean) $stmt->rowCount();
		}
	}