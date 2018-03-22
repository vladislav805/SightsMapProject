<?

	/** @var MainController $mainController */

	use Method\APIException;
	use Model\Params;

	try {
		/** @var Model\Point $info */
		$info = $mainController->perform(new Method\Point\GetById((new Params)->set("pointId", $id)));

		$name = get("name");
		if ($name && $name !== getTransliteratedNamePlace($info)) {
			throw new APIException(ERROR_POINT_NOT_FOUND);
		}

	} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
		echo "Place not found";
		exit;
	}

	/** @var Model\User $owner */
	$owner = $mainController->perform(new Method\User\GetById((new Model\Params)->set("userIds", $info->getOwnerId())));

	/** @var Model\Photo[] $photos */
	$photos = $mainController->perform(new Method\Photo\Get((new Model\Params)->set("pointId", $id)));

	/** @var Model\ListCount $comments */
	$comments = $mainController->perform(new Method\Comment\Get((new Model\Params)->set("pointId", $id)));

	$params = new Model\Params;
	$params
		->set("lat", $info->getLat())
		->set("lng", $info->getLng())
		->set("count", 5)
		->set("distance", 2);

	/** @var Model\ListCount $nearby */
	$nearby = $mainController->perform(new Method\Point\GetNearby($params));

	printf("<h3>%s</h3>", htmlspecialchars($info->getTitle()));
	$url = sprintf("https://static-maps.yandex.ru/1.x/?pt=%.6f,%.6f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1.2", $info->getLng(), $info->getLat());
	printf("<div class='info-map'><img src=\"%s\" alt=\"Карта\" /></div>", $url);
	printf("<p>%s</p>", str_replace("\n", "</p><p>", htmlspecialchars($info->getDescription())));
	printf("<p><strong>Автор</strong>: <a href=\"/user/%1\$s\">@%1\$s</a></p>", htmlspecialchars($owner->getLogin()));
	printf("<p><strong>Добавлено</strong>: %s</p>", date("d.m.Y H:i", $info->getDate()));
	if ($info->getDateUpdated()) {
		printf("<p><strong>Отредактировано</strong>: %s</p>", date("d.m.Y H:i", $info->getDateUpdated()));
	}

	printf("<h4>Фотографии</h4>");

	if (sizeOf($photos)) {
		foreach ($photos as $photo) {
			printf("<a href=\"#photo%d_%d\"><img src=\"%s\" alt='' data-src-big='%s' /></a>", $photo->getOwnerId(), $photo->getId(), $photo->getUrlThumbnail(), $photo->getUrlOriginal());
		}
	} else {
		printf("Нет ни одной фотографии.. :(");
	}

	printf("<h4>Комментарии</h4>");

	if ($comments->getCount()) {
		foreach ($comments->getItems() as $c) {
			/** @var \Model\Comment $c */
			printf("%d, %s", $c->getId(), $c->getText());
		}
	} else {
		printf("Нет комментариев");
	}

	if ($nearby->getCount()) {
		$items = $nearby->getItems();
		$data = $nearby->getCustomData("distances");
		$distances = [];

		foreach ($data as $k) {
			$distances[$k["pointId"]] = $k["distance"];
		}

		printf("<h4>А неподалёку отсюда есть...</h4>");
		printf("<div class='suggestPlace-list'>");
		foreach ($items as $item) {
			/** @var \Model\Point $item */
			printf("<a class='suggestPlace' href=\"%s\"><div class='suggestPlace-distance'>%.2f км</div><h5>%s</h5><p>%s</p></a>", getHumanizeURLPlace($item), $distances[$item->getId()], htmlspecialchars($item->getTitle()), htmlspecialchars(mb_substr($item->getDescription(), 0, 60)));
		}
		printf("</div>");
	}

	echo "<pre>";
//	var_dump($info);
	echo "</pre>";
