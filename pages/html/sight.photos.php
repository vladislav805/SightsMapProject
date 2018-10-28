<div class="sight-photos">
	<h4>Фотографии</h4>
	<div class="sight-photos-items">
<?
	$_users = [];

	/** @var \Model\User $user */
	foreach ($photos["users"] as $user) {
		$_users[$user->getId()] = $user;
	}

	/** @var \Model\Photo[] $photos */
	$photos = $photos["items"];

	if (sizeOf($photos)) {
		foreach ($photos as $photo) {
			/** @var \Model\User $author */
			$author = $_users[$photo->getOwnerId()];
?>
<a href="<?=$photo->getUrlOriginal();?>" data-caption="<?=htmlSpecialChars(sprintf('Загружено %s пользователем <a href="/user/%s" target="_blank">@%s</a>', getRelativeDate($photo->getDate()), $author->getLogin(), $author->getLogin()));?>">
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