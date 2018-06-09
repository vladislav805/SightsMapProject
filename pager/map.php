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
<div class="map" id="map"></div>
<div class="map-filter">
	<div class="fi-wrap">
		<select name="mm-verified" id="mm-verified" class="mm-fd">
			<option value="-1">все</option>
			<option value="0">только неподтвержденные</option>
			<option value="1">только подтвержденные</option>
		</select>
		<label for="mm-verified">Статус подтверждения</label>
	</div>
<?
	if ($mainController->isAuthorized()) {
?>
	<div class="fi-wrap">
		<select name="mm-state" id="mm-state" class="mm-fd">
			<option value="-1">без фильтра</option>
			<option value="0">только непосещенные</option>
			<option value="1">только посещенные</option>
			<option value="2">только желаемые</option>
		</select>
		<label for="mm-state">Статус посещения</label>
	</div>
<?
	}
?>
</div>
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
<script src="/js/api.js"></script>
<script src="/js-pager/utils.js"></script>
<script src="/js-pager/app.js"></script>
<script src="/lib/sugar.min.js"></script>
<script async src="/lib/baguetteBox.min.js"></script>
<script>
	API.session.setAuthKey(<?=json_encode($mainController->getAuthKey());?>);
	XMap.init();
</script>
<?
	require_once "__footer.php";