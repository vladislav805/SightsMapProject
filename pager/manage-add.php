<?

	/** @var MainController $mainController */

	use Model\City;
	use Model\ListCount;
	use Model\Mark;

	if (!$mainController->isAuthorized()) {
		redirectTo("/");
	}

	$getTitle = function() {
		return "Добавление места | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Добавление места",
			//"description" => "",
			"image" => "",
			"type" => "website"
		];
	};

	/** @var ListCount $marksList */
	$marksList = $mainController->perform(new \Method\Mark\Get([]));

	/** @var ListCount $citiesList */
	$citiesList = $mainController->perform(new \Method\City\Get([]));

	/** @var Mark[] $marks */
	$marks = $marksList->getItems();

	/** @var City[] $cities */
	$cities = $citiesList->getItems();

	require_once "__header.php";
?>
	<h3>Добавление места</h3>

	<!--suppress HtmlUnknownTarget -->
	<form action="/place/add" method="post" enctype="multipart/form-data" class="manage-map-wrap">

		<div id="manage-map"></div>

		<div class="manage-content">
			<div class="fi-wrap">
				<input type="text" name="title" id="m-title" pattern=".+" required="required" />
				<label for="m-title">Название</label>
			</div>
			<div class="fi-wrap">
				<textarea name="description" id="m-description" required="required"></textarea>
				<label for="m-description">Описание (необязательно)</label>
			</div>
			<div class="fi-wrap">
				<select name="city" id="m-city">
<?
	foreach ($cities as $city) {
?>
					<option value="<?=$city->getId();?>"><?=htmlspecialchars($city->getName());?></option>
<?
	}
?>
				</select>
				<label for="m-city">Название</label>
			</div>
			<div class="manage-marks-wrap">
				<div class="fi-label">Метки</div>
				<div class="manage-marks-items">
<?
	foreach ($marks as $mark) {
?>
					<label class="mark-colorized" style="--colorMark: #<?=getHexColor($mark->getColor());?>">
						<input type="checkbox" name="markId[]" value="<?=$mark->getId();?>" />
						<span style="--color"><?=htmlspecialchars($mark->getTitle());?></span
					</label>
<?
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

	<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
	<script src="/lib/exif-js.min.js"></script>
	<script src="/js-pager/utils.js"></script>
	<script src="/js-pager/manage.js"></script>
	<script src="/lib/sugar.min.js"></script>
<?
	require_once "__footer.php";