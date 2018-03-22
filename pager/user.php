<?

	/** @var MainController $mainController */

	use Method\APIException;
	use Model\Params;

	try {
		$id = get("id"); // string (login) will be 0
		/** @var Model\User $info */
		$info = $mainController->perform(new Method\User\GetById((new Params)->set("userId", $id)));
	} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
		echo "User not found";
		exit;
	}

	$params = new Params;
	$params
		->set("ownerId", $info->getId())
		->set("offset", (int) get("offset"))
		->set("count", 20);

	$owner = $mainController->perform(new Method\Point\Get($params));

	echo "<pre>";
	var_dump($info);
	var_dump($owner);
	echo "</pre>";
