<?php

	namespace Method\User;

	use Method\APIPublicMethod;
	use Model\IController;

	/**
	 * @package Method\User
	 */
	class Activate extends APIPublicMethod {

		/** @var  int */
		protected $status;

		public function __construct($request) {
			parent::__construct($request);
		}

		/**
		 * @param IController $main
		 * @return boolean
		 */
		public function resolve(IController $main) {
			$stmt = $main->makeRequest("UPDATE `user` SET `status` = ? WHERE `userId` = ?");

			$stmt->execute([$this->status ? time() : 0, $main->getSession()->getUserId()]);

			return (boolean) $stmt->rowCount();
		}
	}