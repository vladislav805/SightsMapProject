<?

	/** @var MainController $mainController */

	use Method\APIException;
	use Model\Comment;
	use Model\Mark;
	use Model\Params;
	use Model\User;

	try {
		/** @var Model\Point $info */
		$info = $mainController->perform(new Method\Point\GetById((new Params)->set("pointId", $id)));

		$name = get("name");
		if ($name && $name !== getTransliteratedNamePlace($info)) {
			throw new APIException(ERROR_POINT_NOT_FOUND);
		}

	} /** @noinspection PhpRedundantCatchClauseInspection */ catch (APIException $e) {
		echo "Place not found";
		var_dump($e);
		exit;
	}

	/** @var Model\User $owner */
	$owner = $mainController->perform(new Method\User\GetById((new Model\Params)->set("userIds", $info->getOwnerId())));

	$args = (new Model\Params)->set("pointId", $id);

	/** @var Model\Photo[] $photos */
	$photos = $mainController->perform(new Method\Photo\Get($args));

	/** @var Model\ListCount $comments */
	$comments = $mainController->perform(new Method\Comment\Get($args));

	/** @var array $stats */
	$stats = $mainController->perform(new Method\Point\GetVisitCount($args));

	$params = new Model\Params;
	$params
		->set("lat", $info->getLat())
		->set("lng", $info->getLng())
		->set("count", 6)
		->set("distance", 2);

	/** @var Model\ListCount $nearby */
	$nearby = $mainController->perform(new Method\Point\GetNearby($params));

	/** @var Mark[] $marks */
	$marks = $mainController->perform(new Method\Mark\GetByPoint($args));

	$getTitle = function() use ($info) {
		return $info->getTitle() . " | Sights";
	};

	$urlImage = htmlspecialchars(sprintf("https://static-maps.yandex.ru/1.x/?pt=%.6f,%.6f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1", $info->getLng(), $info->getLat()));
	$urlLink = htmlspecialchars(sprintf("/map?c=%.6f_%.6f&z=15&id=%d", $info->getLat(), $info->getLng(), $info->getId()));
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

	if (sizeOf($photos)) {
		$getRibbon = function () use ($photos) {
			return $photos[0]->getUrlOriginal();
		};
	}

	require_once "__header.php";
?>
	<h3><?=htmlspecialchars($info->getTitle());?></h3>

	<div class="info-map">
		<a href="<?=$urlLink;?>"><img src="<?=$urlImage;?>" alt="Карта" /></a>
<?
	if ($info->getCity()) {
?>
		<p><a href="/city/<?=$info->getCity()->getId();?>"><?=htmlspecialchars($info->getCity()->getName());?></a></p>
<?
	}
?>

	</div>
<?
	if ($info->isVerified()) {
?>
		<div class="place-verified-string"><i class="material-icons"></i> Подтвержденное место</div>
<?
	}
?>
	<div class="place-marks">
<?
	foreach ($marks as $mark) {
?>
		<a href="/mark/<?=$mark->getId();?>" class="mark-colorized" style="--colorMark: #<?=getHexColor($mark->getColor());?>"><?=htmlspecialchars($mark->getTitle());?></a>
<?
	}
?>
	</div>
	<p><?=str_replace("\n", "</p><p>", htmlspecialchars($info->getDescription()));?></p>
	<p><?=($owner->getSex() === 1 ? "Добавила" : "Добавил");?> <a href="/user/<?=$login;?>">@<?=$login;?></a> <?=getRelativeDate($info->getDate());?><?=($info->getDateUpdated() ? " <span class='info-dateUpdated'>(ред. " . getRelativeDate($info->getDateUpdated()) . ")</span>" : "");?></p>
	<h5>Статистика</h5>
	<p>Рейтинг: <?=sprintf("%.1f/10.0", $info->getRating());?></p>
	<p>Посетили <?=sprintf("%d %s", $stats["visited"], pluralize($stats["visited"], "человек", "человека", "человек"));?></p>
	<p>Хотят посетить <?=sprintf("%d %s", $stats["desired"], pluralize($stats["desired"], "человек", "человека", "человек"));?></p>

	<h4>Фотографии</h4>
	<div class="place-photos">
<?
	if (sizeOf($photos)) {
		foreach ($photos as $photo) {
?>
		<a href="<?=$photo->getUrlOriginal();?>" data-caption="Загружено <?=getRelativeDate($photo->getDate());?>"><img src="<?=$photo->getUrlThumbnail();?>" alt='' data-src-big='<?=$photo->getUrlOriginal();?>' /></a>
<?
		}
	} else {
		printf("Нет ни одной фотографии.. :(");
	}
?>
	</div>

	<h4>Комментарии</h4>
	<div class="comments-items">
<?
	if ($comments->getCount()) {
		$users = [];
		/** @var User[] $u */
		$u = $comments->getCustomData("users");
		foreach ($u as $item) {
			$users[$item->getId()] = $item;
		}
		$u = null;
		/** @var Comment[] $cItems */
		$cItems = $comments->getItems();
		foreach ($cItems as $c) {
			/** @var User $u */
			$u = $users[$c->getUserId()];
?>
<div class="comment-item">
	<div class="comment-author-photo" style="background-image: url('<?=$u->getPhoto()->getUrlThumbnail();?>')"></div>
	<div class="comment-content">
		<h6 class="comment-author-name"><?=htmlspecialchars($u->getFirstName() . " " . $u->getLastName());?></h6>
		<div class="comment-text">
			<?=htmlspecialchars($c->getText());?>
		</div>
		<div class="comment-footer">
			<?=getRelativeDate($c->getDate());?>
		</div>
	</div>
</div>
<?
		}
	} else {
		printf("Нет комментариев");
	}
?>
	</div>
<?

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
			printf("<a class='suggestPlace' href=\"%s\"><div class='suggestPlace-distance'>%s</div><h5>%s</h5><p>%s</p></a>", getHumanizeURLPlace($item), getHumanizeDistanceString($distances[$item->getId()]), htmlspecialchars($item->getTitle()), htmlspecialchars(truncate($item->getDescription(), 60)));
		}
?>
	</div>
	<script src="/lib/baguetteBox.min.js"></script>
	<script>
		baguetteBox.run(".place-photos a", {
			noScrollbars: true,
			async: true
		});
	</script>
<?
	}
	require_once "__footer.php";