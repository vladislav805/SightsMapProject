<?

	use Model\ListCount;
	use Model\Photo;
	use Model\User;

	/** @var \Pages\SightPage $this */
	/** @var \Model\Sight $info */

	$_users = [];

	/** @var ListCount $photos */

	/** @var User $user */
	foreach ($photos->getCustomData("users") as $user) {
		$_users[$user->getId()] = $user;
	}
?>

<div class="sight-photos">
<?

	$header = "";

	if (!$photos->getCount()) {
		if ($this->getCurrentUser()) {
			if ($this->getCurrentUser()->getId() === $info->getOwnerId()) {
				$header = sprintf(" <a class=\"button\" href=\"/sight/%d/edit\">Добавить фотографию</a>", $info->getId());
			} else {
				$header = sprintf(" <button onclick=\"SightPage.openDialogSuggestPhoto(+this.dataset.sightId);\" data-sight-id=\"%d\">Предложить фото</button>", $info->getId());
			}
		}
	}
?>
	<h4>Фотографии<?=$header;?></h4>
	<div class="sight-photos-list">
<?


	$photos = $photos->getItems();

	/** @var Photo[] $photos */
	if (sizeOf($photos)) {
		$photoTypes = [null, "Фотографии", null, "Предложенные пользователями"];

		$lastPhotoType = Photo::TYPE_SIGHT;

		foreach ($photos as $photo) {

			if ($photo->getType() !== $lastPhotoType) {
				printf("</div><h4>%s</h4><div class=\"sight-photos-list\">", $photoTypes[$photo->getType()]);
				$lastPhotoType = $photo->getType();
			}

			/** @var User $author */
			$author = $_users[$photo->getOwnerId()];
			$caption = sprintf(
					'Загружено %s пользователем <a href="/user/%s" target="_blank">@%s</a>',
					getRelativeDate($photo->getDate()),
					$author->getLogin(),
					$author->getLogin()
			);
			$className = ["sight-photoItem"];

			if ($photo->getType() === Photo::TYPE_SIGHT_SUGGESTED) {
				$caption = "<span class='sight-photo-caption--warning'>Фотография не подтверждена автором места или администратором!</span><br>" . $caption;
				$className[] = "sight-photoItem--suggested";
			}
?>
<a
		class="<?=join(" ", $className);?>"
		href="<?=$photo->getUrlOriginal();?>"
		data-caption="<?=htmlSpecialChars($caption);?>"
		data-photo-id="<?=$photo->getId();?>"
		data-noAjax>
	<img
			src="<?=$photo->getUrlThumbnail();?>"
			alt=""
			data-src-big="<?=$photo->getUrlOriginal();?>" />
</a>
<?
		}
	} else {
		print "Нет ни одной фотографии.. :(";
	}
?>
	</div>
</div>