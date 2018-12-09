<?
	/** @var \Model\User $info */
	/** @var \Model\ListCount $places */
?><div class="profile-info">
	<div class="profile-photo" style="background-image: url('<?=$info->getPhoto()->getUrlThumbnail();?>');"></div>
	<div class="profile-cost-collaboration"><?
	printf("%s %s %d %s", $info->getFirstName(), getGenderWord($info, "добавил", "добавила"), $places->getCount(), pluralize($places->getCount(), "место", "места", "мест"));
?></div>
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
