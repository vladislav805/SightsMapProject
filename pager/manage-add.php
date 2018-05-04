<?

	/** @var MainController $mainController */

	use Model\ListCount;
	use Model\Mark;

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

	/** @var Mark[] $marks */
	$marks = $marksList->getItems();

	require_once "__header.php";
?>
	<h3>Добавление места</h3>

	<!--suppress HtmlUnknownTarget -->
	<form action="/place/add" method="post" enctype="multipart/form-data"  class="manage-map-wrap">

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
			<div class="manage-marks-wrap">
				<div class="fi-label">Метки</div>
				<div class="manage-marks-items">
<?
	foreach ($marks as $mark) {
?>
					<label><input type="checkbox" name="markId[]" value="<?=$mark->getId();?>" /> <span><?=htmlspecialchars($mark->getTitle());?></span></label>
<?
	}
?>
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