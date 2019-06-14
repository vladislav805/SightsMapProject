<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
	/** @var int $notificationCount */

	$backUrl = "";
	if ($this instanceof \Pages\WithBackLinkPage) {
		$backUrl = $this->getBackURL($data);
	}
?>
<div id="head" class="<?=($this instanceOf \Pages\RibbonPage && $this->hasRibbon($data) ? "head--ribbon" : "");?>">
	<div class="head-left">
		<a id="head-logo" class="head-element" href="/" data-noAjax></a>
		<a id="head-back" class="material-icons head-element" href="<?=$backUrl;?>">arrow_back</a>
	</div>

	<div id="head-user">
		<a class="material-icons head-element" href="/sight/random">style</a>
<? if ($this->mController->getSession()) { ?>
		<a class="material-icons head-element" href="/sight/add">add_location</a>
<? } ?>
		<div class="head-user head-element">
<?
	if ($this->mController->getSession()) {
		$u = $this->mController->getUser();
?>
			<a href="/user/<?=$u->getLogin();?>" title="<?=htmlSpecialChars($u->getFirstName() . " " . $u->getLastName());?>" class="head-user-photo-thumbnail" id="hatPhoto" style="background-image: url('<?=htmlSpecialChars($u->getPhoto()->getUrlThumbnail());?>')"></a>
<?
	} else {
?>
			<div onclick="openLoginForm();" class="head-user-auth" data-noAjax><i class="material-icons">account_box</i></div>
<?
	}
?>
		</div>
	</div>
</div>

<div class="page-content">
<?
	require_once "default.ribbon.php";
?>
	<div class="page-content-wrap">
		<div class="page-content-menu">
			<menu class="main-menu">
<?
	if ($this->mController->getSession()) {
		$u = $this->mController->getUser();
?>
				<li><a href="/user/<?=$u->getLogin();?>">Профиль</a></li>
				<li><a href="/map">Карта</a></li>
				<li><a href="/sight/search">Поиск</a></li>
				<li><a href="/sight/random">Случайное место</a></li>
				<li><a href="/feed" id="head-events" data-feed-count="<?=$notificationCount ?? 0;?>">События</a></li>
				<li><a href="/neural">Нейронка</a></li>
				<li class="menu-item--userarea"><a href="/login?action=logout&amp;repath=<?=htmlSpecialChars(get_http_request_uri());?>" data-noAjax>Выход</a></li>
<?
	} else {
?>
				<li><a href="/">Главная</a></li>
				<li><a href="/map">Карта</a></li>
				<li><a href="/sight/search">Поиск</a></li>
				<li><a href="/sight/random">Случайное место</a></li>
				<li><a class="menu-item--userarea" href="/login" data-noAjax onclick="return openLoginForm();">Вход / Регистрация</a></li>
<?
	}
?>
				<li><a href="/docs">API</a></li>
			</menu>
		</div>
		<div class="page-content-inner">