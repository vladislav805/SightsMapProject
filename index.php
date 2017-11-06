<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title>Sights</title>
		<link rel="stylesheet" href="css/styles.css" />
		<link rel="stylesheet" href="css/ui.css" />

		<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
		<script src="js/common.js"></script>
		<script src="js/api.js"></script>
		<script src="lib/sugar.min.js"></script>
		<script src="js/controllers/Map.js"></script>
		<script src="js/controllers/Marks.js"></script>
		<script src="js/controllers/Aside.js"></script>
		<script src="js/model/AsidePage.js"></script>
		<script src="js/controllers/Points.js"></script>
		<script src="js/controllers/Photos.js"></script>
		<script src="js/controllers/Comments.js"></script>
		<script src="js/model.js"></script>
		<script src="js/model/Filter.js"></script>
		<script src="js/model/Bundle.js"></script>
		<script src="js/model/User.js"></script>
		<script src="js/model/Place.js"></script>
		<script src="js/model/Point.js"></script>
		<script src="js/model/Session.js"></script>
		<script src="js/model/Photo.js"></script>
		<script src="js/model/Mark.js"></script>
		<script src="js/model/Comment.js"></script>
		<script src="js/model/PointListItem.js"></script>
		<script src="js/ui/Modal.js"></script>
		<script src="js/ui/Select.js"></script>
		<script src="js/ui/SelectItem.js"></script>
		<script src="js/ui/TabWrap.js"></script>
		<script src="js/ui/Tab.js"></script>
		<script src="js/ui/Toast.js"></script>
		<script src="js/app.js"></script>
		<script src="js/ui.js"></script>
		<script src="js/profile.js"></script>
		<script src="lib/baguetteBox.min.js"></script>
	</head>
	<body class="user-unauthorized">
		<div id="head">
			<div id="head-logo"><i class="material-icons">&#xe55b;</i> Sights Map</div>
			<div id="head-events" class="head-events __user-authorized material-icons">&#xe7f4;</div>
			<div id="head-user">
				<div class="head-user __user-authorized">
					<div class="head-user-info">
						<h3 id="hatName"></h3>
						<h4 id="hatLogin"></h4>
					</div>
					<img class="head-user-photo" id="hatPhoto" src="" alt="" />
					<div class="head-dd-menu">
						<div class="head-dd-item" onclick="Profile.showSettings();">Настройки профиля</div>
						<div class="head-dd-item" onclick="Main.closeSession();">Выход</div>
					</div>
				</div>
				<div class="head-user __user-unauthorized" onclick="Profile.showLogin();">Авторизация &raquo;</div>
			</div>
		</div>
		<div id="content">
			<div id="mapGroup">
				<div id="map"></div>
				<div id="mapOptions">
					<div class="x-select-wrap" id="mapOptionCategories">
						<div class="x-select-label"><i class="material-icons">&#xE53B;</i> <span class="x-select-value">Категории</span></div>
						<div class="x-select-items"></div>
					</div>
					<div class="x-select-wrap __user-authorized" id="mapOptionVisited">
						<div class="x-select-label"><i class="material-icons">&#xe566;</i> <span class="x-select-value">Все</span></div>
						<div class="x-select-items"></div>
					</div>

				</div>
			</div>
			<div id="aside">

			</div>
		</div>
	</body>
</html>