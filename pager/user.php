<?

	/** @var MainController $mainController */

	use Method\APIException;
	use Model\Params;
	use Model\Point;

	try {
		$id = get("id"); // string (login) will be 0
		/** @var Model\User $info */
		$info = $mainController->perform(new Method\User\GetById((new Params)->set("userId", $id)));
		if (!$info) {
			throw new APIException(ERROR_NO_PARAM);
		}
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
	$ownPlaces = $mainController->perform(new Method\Point\GetOwns($params));

	$getTitle = function() use ($info) {
		return $info->getFirstName() . " " . $info->getLastName() . " | Sights";
	};

	$getOG = function() use ($info) {
		return [
			"title" => "Профиль @" . $info->getLogin(),
			"description" => $info->getFirstName() . " " . $info->getLastName(),
			"image" => $info->getPhoto()->getUrlOriginal(),
			"type" => "profile",
			"profile:first_name" => $info->getFirstName(),
			"profile:last_name" => $info->getLastName(),
			"profile:username" => $info->getLogin(),
			"profile:gender" => $info->getSex() === 1 ? "female" : "male"
		];
	};

	require_once "__header.php";

	$status = [];

	if ($info->getCity()) {
		$status[] = htmlspecialchars($info->getCity()->getName());
	}

	$status[] = $info->isOnline()
		? "online"
		: sprintf("%s на сайте %s", $info->getSex() === 1 ? "была" : "был", getRelativeDate($info->getLastSeen()));

	if (sizeOf($status)) {
		$status[0] = upperCaseFirstLetter($status[0]);
	}
?>

<div class='profile-info'>
	<div class='profile-photo' style="background-image: url('<?=$info->getPhoto()->getUrlThumbnail();?>');"></div>
	<h3 class='profile-login'>@<?=htmlspecialchars($info->getLogin());?></h3>
	<h5 class='profile-fullName'><?=htmlspecialchars($info->getFirstName() . " " . $info->getLastName());?></h5>
	<p class='profile-lastSeen'><?=join("; ", $status);?></p>
</div>
<h4>Места, которые <?=$info->getSex() === 1 ? "добавила" : "добавил";?> <?=htmlspecialchars($info->getFirstName());?>:</h4>
<?
	if ($ownPlaces->getCount()) {
		/** @var Point[] $items */
		$items = $ownPlaces->getItems();
		printf("<div class='suggestPlace-list'>");
		foreach ($items as $item) {
			/** @var Point $item */
			printf("<a class='suggestPlace' href=\"%s\"><h5>%s</h5><p>%s</p></a>", getHumanizeURLPlace($item), htmlspecialchars($item->getTitle()), htmlspecialchars(truncate($item->getDescription(), 60)));
		}
		if ($ownPlaces->getCount() !== sizeOf($ownPlaces->getItems())) {
			$sub = $ownPlaces->getCount() - sizeOf($ownPlaces->getItems());
			printf(
				"... и еще %d %s",
				$sub,
				pluralize($sub, "место", "места", "мест")
			);
		}
		printf("</div>");
	} else {
		printf("<p>Ничего нет :(</p>");
	}

	require_once "__footer.php";