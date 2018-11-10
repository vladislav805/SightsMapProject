<?
	/** @var \Model\Mark[] $marks */
	/** @var array[] $cities */
	/** @var \Model\Point $sight */
?>
<form action="#" method="post" enctype="multipart/form-data" class="manage-map-wrap" id="__manageMapForm">

	<div id="manage-map"></div>

	<div class="manage-content">
		<?=new \UI\StylisedInput("title", "Название", "m-title", $sight->getTitle());?>
		<?=(new \UI\StylisedInput("description", "Описание (необязательно)"))->setType("textarea")->setValue($sight->getDescription());?>
		<?=new \UI\StylisedSelect("city", "Город", $cities);?>
		<div class="manage-marks-wrap">
			<div class="fi-label">Метки</div>
			<div class="manage-marks-items">
<?
	$markIds = $sight->getMarkIds();
	foreach ($marks as $mark) {
		print new \UI\StylisedCheckbox("markId[]", $mark->getTitle(), in_array($mark->getId(), $markIds), $mark->getId(), null, getHexColor($mark->getColor()));
	}
?>
			</div>
		</div>
		<div class="manage-photos-wrap">
			<div class="fi-label">Фотографии</div>
			<p>Огромая просьба загружать только фотографии, сделанные лично Вами! Фотографии из Интернета не принимаются во внимание!</p>
			<div class="manage-photos-list" data-count="0"></div>
			<div class="manage-photos-dropZone" id="__photo-drop-zone" data-label-empty="Нажмите здесь для выбора файла или бростье сюда файл для определения местоположения">
				<input type="file" id="fileElem" accept="image/*" multiple="multiple" />
			</div>
		</div>
		<div class="manage-footer">
			<input type="submit" value="Сохранить" />
		</div>
	</div>
</form>