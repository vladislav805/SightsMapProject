<div class="sight-photos">
	<h4>Фотографии</h4>
	<div class="sight-photos-items">
<?

	use Model\ListCount;
	use Model\Photo;
	use Model\User;

	$_users = [];

	/** @var ListCount $photos */

	/** @var User $user */
	foreach ($photos->getCustomData("users") as $user) {
		$_users[$user->getId()] = $user;
	}

	$photos = $photos->getItems();
	/** @var Photo[] $photos */

	if (sizeOf($photos)) {
		foreach ($photos as $photo) {
			/** @var User $author */
			$author = $_users[$photo->getOwnerId()];
?>
<a href="<?=$photo->getUrlOriginal();?>" data-caption="<?=htmlSpecialChars(sprintf('Загружено %s пользователем <a href="/user/%s" target="_blank">@%s</a>', getRelativeDate($photo->getDate()), $author->getLogin(), $author->getLogin()));?>" data-noAjax data-photo-id="<?=$photo->getId();?>">
	<img src="<?=$photo->getUrlThumbnail();?>" alt="" data-src-big="<?=$photo->getUrlOriginal();?>" />
</a>
<?
		}
	} else {
		print "Нет ни одной фотографии.. :(";
	}
?>
	</div>
</div>