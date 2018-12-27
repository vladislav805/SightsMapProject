<?php

	namespace Method\Comment;

	use Method\APIPrivateMethod;
	use Model\IController;

	class Report extends APIPrivateMethod {

		/** @var int */
		protected $commentId;

		/**
		 * @param IController $main
		 * @return mixed
		 */
		public function resolve(IController $main) {


			return 0; //$main->perform(new GetById(["markId" => $this->markId]));
		}
	}