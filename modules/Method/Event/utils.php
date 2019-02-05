<?php

	namespace Method\Event;
	use Model\IController;
	use Model\Params;

	/**
	 * @param IController $cnt
	 * @param int $toId User, which need send event
	 * @param int $type Type ID
	 * @param int $subjectId ID of subject, which event was occurred
	 * @return int
	 * @deprecated
	 */
	function sendEvent(IController $cnt, int $toId, int $type, int $subjectId) {
		$p = new Params;
		$p->set("toId", $toId)->set("type", $type)->set("subjectId", $subjectId);
		return $cnt->perform(new Send($p));
	}