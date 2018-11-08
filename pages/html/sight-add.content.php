<?
	/** @var \Model\Mark[] $marks */
	/** @var array[] $cities */
?>
<form action="#" method="post" enctype="multipart/form-data" class="manage-map-wrap">

	<div id="manage-map"></div>

	<div class="manage-content">
		<?=new \UI\StylisedInput("title", "Название", "m-title");?>
		<?=(new \UI\StylisedInput("description", "Описание (необязательно)"))->setType("textarea");?>

		<?=new \UI\StylisedSelect("city", "Город", $cities);?>
		<div class="manage-marks-wrap">
			<div class="fi-label">Метки</div>
			<div class="manage-marks-items">
<?
	foreach ($marks as $mark) {
		print new \UI\StylisedCheckbox("markId[]", $mark->getTitle(), false, $mark->getId(), null, getHexColor($mark->getColor()));
	}
?>
			</div>
		</div>
		<div class="manage-photos-wrap">
			<div class="fi-label">Метки</div>
			<input type="file" id="fileElem" accept="image/*" onchange="ManageMap.handleFiles(this.files)">
			<div class="manage-photos-list" id="__photo-drop-zone">

			</div>
		</div>
		<div class="manage-footer">
			<input type="submit" value="Сохранить" />
		</div>
	</div>

</form>