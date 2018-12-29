<?
	/** @noinspection PhpUndefinedVariableInspection,HtmlUnknownAttribute */
	/** @var \Model\Sight $info */
?>
<div class="sight-information">
	<div class="sight-aside">
		<a href="/map?c=<?=$info->getLat();?>_<?=$info->getLng();?>&amp;z=18" class="sight-mapThumbnail-link" data-lat="<?=$info->getLat();?>" data-lng="<?=$info->getLng();?>" data-pid="<?=$info->getId();?>"></a>
		<div class="sight-actions">
<?
	$isAuth = $this->mController->getSession();
	$isAdmin = $isAuth && in_array($this->mController->getUser()->getStatus(), [\Model\User::STATE_MODERATOR, \Model\User::STATE_ADMIN]);
	if ($isAdmin) {
?>
			<button onclick="Sight.verify(this)" data-pid="<?=$info->getId();?>" data-now-state="<?=(int) $info->isVerified();?>" class="sight-action-verify">Подтверждение = </button>
			<button onclick="Sight.archive(this)" data-pid="<?=$info->getId();?>" data-now-state="<?=(int) $info->isArchived();?>" class="sight-action-archive">Архивирование = </button>
<?
	}

	if ($info->canModify()) {
?>
			<a href="/sight/<?=$info->getId();?>/edit" class="button sight-action-edit">Редактировать</a>
			<button onclick="Sight.remove(this)" data-sid="<?=$info->getId();?>" class="sight-action-remove">Удалить</button>
<?
	}
?>
		</div>
	</div>
	<div class="sight-description">
		<h5>Описание</h5>
		<p><?=formatText($info->getDescription());?></p>
	</div>

	<div class="sight-marks-list">
<?
	/** @var \Model\Mark $mark */
	foreach ($marks as $mark) {
		printf('<a href="/sight/search?markIds=%d" class="sight-mark-item-colorized" style="--colorMark: %s">%s</a>', $mark->getId(), getHexColor($mark->getColor()), htmlSpecialChars($mark->getTitle()));
	}
?>
	</div>

	<div class="sight-meta">
		<p>Добавлено <?=getRelativeDate($info->getDate()) . ($info->getDateUpdated() ? " и изменено в последний раз " . getRelativeDate($info->getDateUpdated()) . "</span>" : "");?></p>
	</div>

	<div class="sight-statistics">
		<h5>Статистика</h5>
		<p>Рейтинг:&nbsp;
<?
	if ($isAuth) {
?>
			<button class="button material-icons sight-rating--setter" onclick="Sight.setRating(this, -1)" data-pid="<?=$info->getId();?>">thumb_down</button>
<?
	}
?>
			<strong><?=$info->getRating();?></strong>
<?
	if ($isAuth) {
?>
			<button class="button material-icons sight-rating--setter" onclick="Sight.setRating(this, 1)" data-pid="<?=$info->getId();?>">thumb_up</button>
<?
	}
?>
		</p>
<?
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
	</div>
<?
	require_once "sight.photos.php";
	require_once "sight.comments.php";
?>
</div>

