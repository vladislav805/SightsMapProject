<?

	/** @var MainController $mainController */

	try {
		/** @var Model\Point $info */
		$info = $mainController->perform(new Method\Point\GetById((new Model\Params)->set("pointId", $id)));
	} /** @noinspection PhpRedundantCatchClauseInspection */ catch (\Method\APIException $e) {
		echo "Place not found";
		exit;
	}

	/** @var Model\User $owner */
	$owner = $mainController->perform(new Method\User\GetById((new Model\Params)->set("userIds", $info->getOwnerId())));

	/** @var Model\Photo[] $photos */
	$photos = $mainController->perform(new Method\Photo\Get((new Model\Params)->set("pointId", $id)));

	/** @var Model\ListCount<Model\Comment> $comments */
	$comments = $mainController->perform(new Method\Comment\Get((new Model\Params)->set("pointId", $id)));

	$params = new Model\Params;
	$params
		->set("lat", $info->getLat())
		->set("lng", $info->getLng())
		->set("count", 5)
		->set("distance", 2);
	$nearby = $mainController->perform(new Method\Point\GetNearby($params));

	echo "<pre>";
	var_dump(getHumanizeURLPlace($info));
	var_dump($info);
	var_dump($owner);
	var_dump($photos);
	var_dump($comments);
	var_dump($nearby);
	echo "</pre>";
