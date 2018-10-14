<?

	/** @var MainController $mainController */

	/**
	 * @return string
	 */
	$getTitle = function() {
		return "Главная страница | Sights";
	};

	$getOG = function() {
		return [
			"title" => "Главная страница",
			"description" => "Добро пожаловать на сайт, посвященным всяким интересным местам.",
			"image" => "",
			"type" => "website"
		];
	};

	$counts = $mainController->perform(new \Method\Point\GetCounts([]));
?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title><?=(isset($getTitle) && is_callable($getTitle) ? $getTitle() : "Sights");?></title>
		<?=(isset($getOG) && is_callable($getOG) ? makeOG($getOG()) : "");?>
		<link rel="stylesheet" href="/css/pages.css" />
		<link rel="stylesheet" href="/css/ui.css" />
	</head>
	<body class="page-index">
		<div id="head">
			<a id="head-logo" href="/index">
				<i class="material-icons">&#xe55b;</i>
			</a>

			<div id="head-user">
				<? if ($mainController->getSession()) { ?>
					<div id="head-events" data-count="0" class="head-events material-icons head-element">&#xe7f4;</div>
				<? } ?>
				<div class="head-user head-element">
					<?
						if ($mainController->getSession()) {
							$u = $mainController->getUser();
							?>
							<img class="head-user-photo" id="hatPhoto" src="<?=htmlspecialchars($u->getPhoto()->getUrlThumbnail());?>" alt="" />
							<div class="head-dd-menu">
								<a class="head-dd-item" href="/user/<?=$u->getLogin();?>">Профиль</a>
								<a class="head-dd-item" href="/places/my">Места</a>
								<a class="head-dd-item" href="/login?act=logout">Выход</a>
							</div>
							<?
						} else {
							?>
							<a href="/login" class="head-user-auth" onclick="Profile.showLogin();">Авторизация &raquo;</a>
							<?
						}
					?>
				</div>
			</div>
		</div>
		<div class="page-ribbon" id="__index-map"></div>
		<div class="page-content">
			<div class="page-content-wrap">
			<div class="page-content-inner">


				<h3>Главная страница</h3>
				<div class="index-counts">
					<div class="index-count">
						<h4><?=$counts["total"];?></h4>
						<h5>всего мест</h5>
					</div>
					<div class="index-count">
						<h4><?=$counts["verified"];?></h4>
						<h5>подтвержденных</h5>
					</div>
					<div class="index-count">
						<h4><?=$counts["archived"];?></h4>
						<h5>уже не существующих</h5>
					</div>
				</div>
				<h3>Интересно?</h3>
				<p>Вы можете найти места с помощью поиска по словам, либо с помощью интерактивной карты.</p>
				<div class="index-target">
					<!--suppress HtmlUnknownTarget -->
					<form class="index-target-search" action="/place/search" enctype="multipart/form-data">
						<div class="search-wrap-content">
							<div class="fi-wrap">
								<input type="search" name="query" id="m-query" pattern=".+" required="required" />
								<label for="m-query">Название</label>
							</div>
							<input type="submit" value="Поиск" />
						</div>
					</form>
					<div class="index-target-divider"></div>
					<a class="index-target-map" href="/map">Перейти к карте</a>
				</div>
				<div class="index-target">
					<!--suppress HtmlUnknownTarget -->
					<div class="index-target-search">
						Случайное место
					</div>
					<div class="index-target-divider"></div>
					<form class="index-target-search" action="/place/id" enctype="multipart/form-data">
						<div class="search-wrap-content">
							<div class="fi-wrap">
								<input type="number" name="pointId" id="m-placeId" pattern="\d+" required="required" />
								<label for="m-query">Идентификатор sID</label>
							</div>
							<input type="submit" value="Перейти" />
						</div>
					</form>
				</div>

				</div>
			</div>
			<footer>
				<div class="footer-left">
					<div class="footer-logo">Sights map</div>
					<ul>
						<li><a href="/">Главная</a></li>
						<li><a href="/map">Карта</a></li>
						<li><a href="/place/search">Поиск</a></li>
						<li><a href="https://docs.google.com/document/d/18sEUblZnA51Ni_6wAhrqTqCr6if3mkETaEyasnCH1rM/edit?usp=sharing" target="_blank">API</a></li>
					</ul>
				</div>
				<div class="footer-right">
					<ul>
						<li><a href="//velu.ga/" target="_blank">velu.ga</a> &copy; 2015&ndash;2018</li>
					</ul>
				</div>
			</footer>
		</div>
	<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
	<script src="/js-pager/utils.js"></script>
	<script>

		ymaps.ready(function() {
			var map = new ymaps.Map("__index-map", {
				center: [0, 0],
				zoom: 2,
				controls: []
			}, {
				searchControlProvider: "yandex#search",
				suppressMapOpenBlock: true
			});

			ymaps.geolocation.get({
				provider: "yandex",
				mapStateAutoApply: true
			}).then(function(result) {
				var c = result.geoObjects.position;
				//c[1] -= 0.25;
				map.setCenter(c, 9);
				ge("__index-map").classList.add("__index-map-done");
			});
		});
	</script>
	</body>
</html>