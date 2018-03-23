<?

	/** @var MainController $mainController */

	use Method\APIException;
	use Model\Params;
	use Model\Point;

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

	/** @var \Model\ListCount $ownPlaces */
	$ownPlaces = $mainController->perform(new Method\Point\Get($params));

	printf("<div class='profile-info'>");
	printf("<div class='profile-photo' style='background-image: url(%s);'></div>", $info->getPhoto()->getUrlThumbnail());
	printf("<h3 class='profile-login'>@%s</h3>", htmlspecialchars($info->getLogin()));
	printf("<h5 class='profile-fullName'>%s %s</h5>", htmlspecialchars($info->getFirstName()), htmlspecialchars($info->getLastName()));
	printf("<p class='profile-lastSeen'>%s</p>",
		$info->isOnline()
			? "Online"
			: sprintf("%s на сайте %s", $info->getSex() === 1 ? "Была" : "Был", date("d.m.Y H:i"))
	);
	printf("</div>");

	printf("<h4>Места, которые %s %s:</h4>", $info->getSex() === 1 ? "добавила" : "добавил", htmlspecialchars($info->getFirstName()));

	if ($ownPlaces->getCount()) {
		/** @var Point[] $items */
		$items = $ownPlaces->getItems();
		printf("<div class='suggestPlace-list'>");
		foreach ($items as $item) {
			/** @var Point $item */
			printf("<a class='suggestPlace' href=\"%s\"><h5>%s</h5><p>%s</p></a>", getHumanizeURLPlace($item), htmlspecialchars($item->getTitle()), htmlspecialchars(mb_substr($item->getDescription(), 0, 60)));
		}
		if ($ownPlaces->getCount() !== sizeOf($ownPlaces->getItems())) {
			printf("... и еще %d мест(о)", $ownPlaces->getCount() - sizeOf($ownPlaces->getItems()));
		}
		printf("</div>");
	} else {
		printf("<p>Ничего нет :(</p>");
	}

