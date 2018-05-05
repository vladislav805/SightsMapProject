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
	<body>
		<div id="head">
			<div id="head-logo">
				<i class="material-icons">&#xe55b;</i>
			</div>

			<div id="head-user">
				<? if ($mainController->getSession()) { ?>
				<div id="head-events" data-count="0" class="head-events material-icons head-element">&#xe7f4;</div>
				<? } ?>
				<div class="head-user">
					<?
						if ($mainController->getSession()) {
							$u = $mainController->getUser();
					?>
					<div class="head-user-info head-element">
						<h3 id="hatName"><?=htmlspecialchars($u->getFirstName());?></h3>
						<h4 id="hatLogin">@<?=htmlspecialchars($u->getLogin());?></h4>
					</div>
					<img class="head-user-photo" id="hatPhoto" src="<?=htmlspecialchars($u->getPhoto()->getUrlThumbnail());?>" alt="" />
					<div class="head-dd-menu">
						<div class="head-dd-item" onclick="Profile.requestUserInfo(0);">Профиль</div>
						<div class="head-dd-item" onclick="Profile.showSettings();">Настройки профиля</div>
						<div class="head-dd-item" onclick="Main.closeSession();">Выход</div>
					</div>
					<?
						} else {
					?>
					<a href="/login" class="head-user-auth head-element" onclick="Profile.showLogin();">Авторизация &raquo;</a>
					<?
						}
					?>
				</div>
			</div>
		</div>
		<div class="page-ribbon"<?=(isset($getRibbon) && is_callable($getRibbon) ? makeRibbonPoint($getRibbon()) : "")?>></div>
		<div class="page-content">
			<div class="page-content-wrap">
				<div class="page-content-inner">