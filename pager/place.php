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
		->set("count", 6)
		->set("distance", 2);

	/** @var Model\ListCount $nearby */
	$nearby = $mainController->perform(new Method\Point\GetNearby($params));

	$getTitle = function() use ($info) {
		return $info->getTitle() . " | Sights";
	};

	$urlImage = sprintf("https://static-maps.yandex.ru/1.x/?pt=%.6f,%.6f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1.2", $info->getLng(), $info->getLat());
	$urlLink = sprintf("/map?lat=%.6f&lng=%.6f&z=15&id=%d", $info->getLat(), $info->getLng(), $info->getId());
	$login = htmlspecialchars($owner->getLogin());

	$getOG = function() use ($info, $photos, $owner, $urlImage) {
		return [
			"title" => "Место ". $info->getTitle(),
			"description" => $info->getDescription(),
			"image" => sizeof($photos) ? $photos[0]->getUrlOriginal() : $urlImage,
			"type" => "article",
			"article:published_time" => $info->getDate(),
			"article:modified_time" => $info->getDateUpdated(),
			"article:author" => $owner->getFirstName() . " " . $owner->getLastName()
		];
	};

	/*if (sizeOf($photos)) {
		$getRibbon = function () use ($photos) {
			return $photos[0]->getUrlOriginal();
		};
	}*/

	require_once "__header.php";
?>
	<h3><?=htmlspecialchars($info->getTitle());?></h3>

	<div class='info-map'><a href='<?=$urlLink;?>'><img src="<?=$urlImage;?>" alt="Карта" /></a></div>
	<p><?=str_replace("\n", "</p><p>", htmlspecialchars($info->getDescription()));?></p>
	<p><?=($owner->getSex() === 1 ? "Добавила" : "Добавил");?> <a href="/user/<?=$login;?>">@<?=$login;?></a> <?=getRelativeDate($info->getDate());?><?=($info->getDateUpdated() ? " <span class='info-dateUpdated'>(ред. " . getRelativeDate($info->getDateUpdated()) . ")</span>" : "");?></p>

	<h4>Фотографии</h4>
<?
	if (sizeOf($photos)) {
		foreach ($photos as $photo) {
?>
	<a href="#photo<?=$photo->getOwnerId();?>_<?=$photo->getId();?>"><img src="<?=$photo->getUrlThumbnail();?>" alt='' data-src-big='<?=$photo->getUrlOriginal();?>' /></a>
<?
		}
	} else {
		printf("Нет ни одной фотографии.. :(");
	}
?>

	<h4>Комментарии</h4>
<?
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

?>
	<h4>А поблизости есть...</h4>
	<div class='suggestPlace-list'>
<?
		foreach ($items as $item) {
			/** @var \Model\Point $item */
			/** @noinspection HtmlUnknownTarget */
			printf("<a class='suggestPlace' href=\"%s\"><div class='suggestPlace-distance'>%s</div><h5>%s</h5><p>%s</p></a>", getHumanizeURLPlace($item), getHumanizeDistanceString($distances[$item->getId()]), htmlspecialchars($item->getTitle()), htmlspecialchars(mb_substr($item->getDescription(), 0, 60)));
		}
?>
	</div>
<?
	}
	require_once "__footer.php";