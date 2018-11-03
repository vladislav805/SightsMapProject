<div class="sight-information">
	<div class="sight-aside">
		<a href="#map" class="sight-mapThumbnail-link" data-lat="<?=$info->getLat();?>" data-lng="<?=$info->getLng();?>" data-pid="<?=$info->getId();?>"></a>
	</div>
	<div class="sight-description">
		<h5>Описание</h5>
		<p><?=nl2br(htmlspecialchars($info->getDescription()), true);?></p>
	</div>

	<div class="sight-meta">
		<p>Добавлено <?=getRelativeDate($info->getDate()) . ($info->getDateUpdated() ? " и изменено в последний раз " . getRelativeDate($info->getDateUpdated()) . "</span>" : "");?></p>
	</div>

	<div class="sight-statistics">
		<h5>Статистика</h5>
		<p>Рейтинг:
<?
	if ($this->mController->getSession()) {
?>
			<button class="button material-icons sight-rating--setter" onclick="Sight.setRating(this, -1)" data-pid="<?=$info->getId();?>">thumb_down</button>
<?
	}
?>
			<strong><?=$info->getRating();?></strong>
<?
	if ($this->mController->getSession()) {
?>
			<button class="button material-icons sight-rating--setter" onclick="Sight.setRating(this, 1)" data-pid="<?=$info->getId();?>">thumb_up</button>
<?
	}
?>
		</p>
<?
	$isAuth = $this->mController->getSession();
	$visitStateButton = function($id, $icon, $count, $label) use ($info, $isAuth) {
		$code = "";

		if ($isAuth) {
			$code = "onclick=\"Sight.setVisitState(this)\"";
		}

		return sprintf('<button class="button sight-visitState-unit" %s data-pid="%d" data-visit-state="%d">
				<span><i class="material-icons">%s</i> <var>%s</var></span>
				<strong>%s</strong>
			</button>', $code, $info->getId(), $id, $icon, $count, $label);
	};
?>
		<div class="sight-visitState" data-visit-state="<?=$isAuth ? $info->getVisitState() : -1;?>">
<?
	print $visitStateButton(0, "close", "&infin;", "непосещенное");
	print $visitStateButton(1, "check", $stats["visited"], "посещенное");
	print $visitStateButton(2, "directions_run", $stats["desired"], "желаемое");
?>
		</div>
		<p><?=getSchemaByNumber($stats["visited"], "Здесь еще никто не посетил", [
				"Здесь был %d %s",
				"Здесь было %d %s",
				"Здесь были %d %s"
			], ["человек", "человека", "человек"]);?></p>

		<p><?=getSchemaByNumber($stats["desired"], "Это место никто не хочет посетить :(", [
				"Хочет посетить %d %s",
				"Хотят посетить %d %s",
				"Хотят посетить %d %s"
			], ["человек", "человека", "человек"]);?></p>
	</div>

	<div class="sight-marks-list">
<?
	/** @var \Model\Mark $mark */
	foreach ($marks as $mark) {
		printf('<a href="/mark/%d" class="sight-mark-item-colorized" style="--colorMark: #%s">%s</a>', $mark->getId(), getHexColor($mark->getColor()), htmlSpecialChars($mark->getTitle()));
	}
?>
	</div>
<?
	if ($isAuth && $info->getOwnerId() === $this->mController->getUser()->getId()) {
?>
		<button onclick="Sight.move(this)" data-pid="<?=$info->getId();?>" data-lat="<?=$info->getLat();?>" data-lng="<?=$info->getLng();?>" class="button sight-action-move">Уточнить</button>
		<a href="/place/<?=$info->getId();?>/edit" class="button sight-action-edit">Редактировать</a>
		<button onclick="Sight.remove(this)" data-pid="<?=$info->getId();?>" class="button sight-action-remove">Удалить</button>
<?
	}
	require_once "sight.photos.php";
	require_once "sight.comments.php";
?>
</div>
