<?

	/** @var MainController $mainController */

	$getTitle = function() {
		return "Карта достопримечательностей | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Карта достопримечательностей",
			//"description" => "",
			"image" => "",
			"type" => "website"
		];
	};

	require_once "__header.php";
?>
<h3>Карта</h3>
<div class="map" id="map">

</div>
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
<script src="/js/api.js"></script>
<script src="/js-pager/app.js"></script>
<script src="/lib/sugar.min.js"></script>
<script async src="/lib/baguetteBox.min.js"></script>
<script>
	XMap.init();
</script>
<?
	require_once "__footer.php";