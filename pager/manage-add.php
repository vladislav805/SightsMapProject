<?

	/** @var MainController $mainController */

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

	require_once "__header.php";
?>
	<h3>Добавление места</h3>
	<form action="/add" method="post" enctype="multipart/form-data"  class="manage-map-wrap">

		<div id="manage-map"></div>

		<div class="manage-content">
			<h4><label for="m-title">Название</h4>
			<input type="text" name="title" id="m-title" />
			<h4><label for="m-description">Описание</h4>
			<input type="text" name="description" id="m-description" />
		</div>

	</form>

	<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
	<script src="/lib/exif-js.min.js"></script>
	<script src="/js-pager/utils.js"></script>
	<script src="/js-pager/manage.js"></script>
	<script src="/lib/sugar.min.js"></script>
<?
	require_once "__footer.php";