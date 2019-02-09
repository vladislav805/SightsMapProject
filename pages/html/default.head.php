<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
	/** @var int $notificationCount */

	$backUrl = "";
	if ($this instanceof \Pages\WithBackLinkPage) {
		$backUrl = $this->getBackURL($data);
	}
?>
<div id="head" class="<?=($this instanceOf \Pages\RibbonPage ? "head--ribbon" : "");?>">
	<div class="head-left">
		<a id="head-logo" class="head-element" href="/"><i class="material-icons">&#xe55b;</i></a>
		<a id="head-back" class="material-icons head-element" href="<?=$backUrl;?>">arrow_back</a>
	</div>

	<div id="head-user">
		<a class="material-icons head-element" href="/sight/random">style</a>
<? if ($this->mController->getSession()) { ?>
		<a class="material-icons head-element" href="/sight/add">add_location</a>
		<a class="material-icons head-element" href="/sight/search">search</a>
<? } ?>
		<div class="head-user head-element" data-feed-count="<?=$notificationCount;?>">
<?
	if ($this->mController->getSession()) {
		$u = $this->mController->getUser();
?>
			<div class="head-user-photo-thumbnail" id="hatPhoto" style="background-image: url('<?=htmlSpecialChars($u->getPhoto()->getUrlThumbnail());?>')"></div>
			<div class="head-dd-menu">
				<a class="head-dd-item" href="/user/<?=$u->getLogin();?>" data-label="Профиль">account_box</a>
				<a class="head-dd-item" href="/sights/<?=$u->getLogin();?>" data-label="Места">place</a>
				<a class="head-dd-item head-events" id="head-events" href="/feed" data-label="Уведомления">notifications</a>
				<a class="head-dd-item" href="/login?action=logout&amp;repath=<?=htmlSpecialChars($_SERVER["REQUEST_URI"]);?>" data-noAjax data-label="Выход">exit_to_app</a>
			</div>
<?
	} else {
?>
			<a href="/login?repath=<?=htmlSpecialChars($_SERVER["REQUEST_URI"]);?>" class="head-user-auth" data-noAjax><i class="material-icons">account_box</i></a>
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
		<div class="page-content-inner">