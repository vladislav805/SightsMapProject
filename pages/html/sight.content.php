<?
	/** @noinspection PhpUndefinedVariableInspection,HtmlUnknownAttribute */
	/** @var \Model\Sight $info */

	$isAuth = $this->mController->getSession();
	$isAdmin = $isAuth && isTrustedUser($this->mController->getUser());
?>
<div class="sight-information">
	<div class="sight-aside bg-papers">
		<div class="bg-papers-content">
<?
	include "sight.content.stat.php";
	include "sight.content.rating.php";
?>
			<div class="sight-mapThumbnail-link" data-lat="<?=$info->getLat();?>" data-lng="<?=$info->getLng();?>" data-sid="<?=$info->getId();?>" onclick="SightPage.showModalMap(this)">
				<img src="<?=sprintf("https://static-maps.yandex.ru/1.x/?pt=%.8f,%.8f,comma&z=15&l=map&size=300,300&lang=ru_RU&scale=1", $info->getLng(), $info->getLat());?>" alt="Map" />
			</div>
<?
	include "sight.content.actions.php";
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
		<p>Добавлено <?=getRelativeDate($info->getDate()) . ($info->getDateUpdated() ? " и изменено " . getRelativeDate($info->getDateUpdated()) . "</span>" : "");?></p>
	</div>
<?
	include "sight.photos.php";

	if ($info->getChild() || $info->getParent()) {
		include "sight.content.history.php";
	}

	include "sight.comments.php";

?>
</div>
