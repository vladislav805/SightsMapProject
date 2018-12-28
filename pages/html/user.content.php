<?
	/** @var \Model\User $info */
	/** @var \Model\ListCount $places */
	/** @var array $achievements */
?><div class="profile-info">
	<div class="profile-photo" style="background-image: url('<?=$info->getPhoto()->getUrlThumbnail();?>');"></div>
	<div class="profile-cost-collaboration"><?
	$collaboration = [];

	if (($s = $achievements["authorOfAllSights"]) > 0) {
		$aos = sprintf(
				"%s %d %s",
				getGenderWord($info, "добавил", "добавила"),
				$s,
				pluralize($s, ["место", "места", "мест"])
		);

		if (($s = $achievements["authorOfSights"]) > 0) {
			$aos .= sprintf(
					", из них %d %s (%.1f%%)",
					$s,
					pluralize($s, ["подтвержденное", "подтвержденных", "подтвержденных"]),
					$achievements["authorOfSights"] * 100 / $achievements["authorOfAllSights"]
			);
		}

		$collaboration[] = $aos;
	}

	if (($s = $achievements["visitedSights"])) {
		$collaboration[] = sprintf(
				"%s %d %s",
				getGenderWord($info, "посетил", "посетила"),
				$s,
				pluralize($s, ["место", "места", "мест"])
		);
	}

	if (($s = $achievements["photosOfSights"])) {
		$collaboration[] = sprintf(
				"%s %d %s",
				getGenderWord($info, "загрузил", "загрузила"),
				$s,
				pluralize($s, ["фотографию", "фотографии", "фотографий"])
		);
	}

	if (($s = $achievements["comments"])) {
		$collaboration[] = sprintf(
				"%s %d %s",
				getGenderWord($info, "оставил", "оставила"),
				$s,
				pluralize($s, ["комментарий", "комментария", "комментариев"])
		);
	}

	print join("<br>", $collaboration);


?></div>
<?
	if ($this->mController->isAuthorized() && $info->getId() === $this->mController->getUser()->getId()) {
?>
<a href="/userarea/edit">Редактировать информацию</a>
<?
	}
?>
</div>
<h3>Автор мест</h3>
<?
	if ($places->getCount()) {
		/** @var \Model\Sight[] $items */
		$items = $places->getItems();
?>
<div class="singleListPlace-wrap">
<?
		foreach ($items as $item) {
?>
<a class="singleListPlace-item" href="/sight/<?=$item->getId();?>">
	<h5><?=htmlspecialchars($item->getTitle());?></h5>
	<p><?=htmlspecialchars(truncate($item->getDescription(), 60));?></p>
</a>
<?
		}

		if ($places->getCount() !== sizeOf($places->getItems())) {
			print getSchemaByNumber(
				$places->getCount() - sizeOf($places->getItems()),
				"",
				["и еще %d %s", "и еще %d %s", "и еще %d %s"],
				["место", "места", "мест"]
			);
		}
?>
</div>
<?
	} else {
		printf("<p>%s еще не %s ни одного места</p>", $info->getFirstName(), getGenderWord($info, "добавил", "добавила"));
	}
