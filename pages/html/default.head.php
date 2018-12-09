<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
?>
<div id="head" class="<?=($this instanceOf \Pages\RibbonPage ? "head--ribbon" : "");?>">
	<a id="head-logo" href="/index">
		<i class="material-icons">&#xe55b;</i>
	</a>

	<div id="head-user">
		<a class="material-icons head-element" href="/sight/random">style</a>
<? if ($this->mController->getSession()) { ?>
		<a class="material-icons head-element" href="/sight/add">add_location</a>
		<a id="head-events" data-count="0" class="head-events material-icons head-element" href="/feed">notifications</a>
<? } ?>
		<div class="head-user head-element">
<?
	if ($this->mController->getSession()) {
		$u = $this->mController->getUser();
?>
			<div class="head-user-photo-thumbnail" id="hatPhoto" style="background-image: url('<?=htmlSpecialChars($u->getPhoto()->getUrlThumbnail());?>')"></div>
			<div class="head-dd-menu">
				<a class="head-dd-item" href="/user/<?=$u->getLogin();?>">Профиль</a>
				<a class="head-dd-item" href="/sights/<?=$u->getLogin();?>">Места</a>
				<a class="head-dd-item" href="/login?action=logout&amp;repath=<?=htmlSpecialChars($_SERVER["REQUEST_URI"]);?>">Выход</a>
			</div>
<?
	} else {
?>
			<a href="/login?repath=<?=htmlSpecialChars($_SERVER["REQUEST_URI"]);?>" class="head-user-auth"><i class="material-icons">account_box</i></a>
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