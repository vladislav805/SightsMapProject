<?
	/** @var \Pages\ManageMapPage $this */
	/** @var \Model\Mark[] $marks */
	/** @var array[] $cities */
	/** @var \Model\Sight $sight */


	if (isTrustedUser($this->mController->getUser()) && $sight) {
		$stmt = $this->mController->makeRequest("SELECT `pointId` FROM `point` WHERE `pointId` > ? ORDER BY `pointId` LIMIT 1");
		$stmt->execute([$sight->getId()]);

		$next = $stmt->fetch(PDO::FETCH_ASSOC);
		$next = (int) $next["pointId"];
?>
<a href="/sight/<?=$next;?>/edit" style="display: block; line-height: 40px; text-align: center;">Следующее место</a>
<?
	}
?>
<form action="#" method="post" enctype="multipart/form-data" class="manage-map-wrap" id="__manageMapForm">

	<div id="manage-map"></div>

	<div class="manage-content">
		<?=new \UI\StylisedInput("title", "Название", "m-title", $sight ? $sight->getTitle() : "");?>
		<?=(new \UI\StylisedInput("description", "Описание (необязательно)"))->setType("textarea")->setValue($sight ? $sight->getDescription() : "")->setIsRequired(false);?>
		<?=new \UI\StylisedSelect("cityId", "Город", $cities);?>
		<div class="manage-marks-wrap">
			<div class="fi-label">Метки</div>
			<div class="manage-marks-items">
<?
	$markIds = $sight ? $sight->getMarkIds() : [];
	foreach ($marks as $mark) {
		print new \UI\StylisedCheckbox("markId[]", $mark->getTitle(), in_array($mark->getId(), $markIds), $mark->getId(), null, getHexColor($mark->getColor()));
	}
?>
			</div>
		</div>
		<div class="manage-photos-wrap">
			<div class="fi-label">Фотографии</div>
			<!--p>Огромая просьба загружать только фотографии, сделанные лично Вами! Фотографии из Интернета не принимаются во внимание!</p-->
			<div class="manage-photos-list" data-count="0"></div>
			<div class="manage-photos-dropZone" id="__photo-drop-zone" data-label-empty="Нажмите здесь или бростье сюда файл">
				<input type="file" id="fileElem" accept="image/*" multiple="multiple" />
			</div>
		</div>
		<div class="manage-footer">
			<input type="submit" value="Сохранить" />
		</div>
	</div>
</form>
<div class="manage-suggestions" hidden="hidden">
	<h5>С поставленным местом уже рядом есть места</h5>
	<div class="suggestion-list" id="manage-suggestions"></div>
</div>