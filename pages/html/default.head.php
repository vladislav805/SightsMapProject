<div id="head" class="<?=($this instanceOf \Pages\RibbonPage ? "head--ribbon" : "");?>">
	<a id="head-logo" href="/index">
		<i class="material-icons">&#xe55b;</i>
	</a>

	<div id="head-user">
		<a class="material-icons head-element" href="/place/random">style</a>
<? if ($this->mController->getSession()) { ?>
		<a id="head-events" data-count="0" class="head-events material-icons head-element" href="/feed">&#xe7f4;</a>
<? } ?>
		<div class="head-user head-element">
<?
	if ($this->mController->getSession()) {
		/** @var \Model\User $u */
		$u = $this->mController->getUser();
?>
			<div class="head-user-photo-thumbnail" id="hatPhoto" style="background-image: url('<?=htmlSpecialChars($u->getPhoto()->getUrlThumbnail());?>')"></div>
			<div class="head-dd-menu">
				<a class="head-dd-item" href="/user/<?=$u->getLogin();?>">Профиль</a>
				<a class="head-dd-item" href="/places/my">Места</a>
				<a class="head-dd-item" href="/login?action=logout">Выход</a>
			</div>
<?
	} else {
?>
			<a href="/login" class="head-user-auth"><i class="material-icons">account_box</i></a>
<?
	}
?>
		</div>
	</div>
</div>

<div class="page-content">
<?
	if ($this instanceof \Pages\RibbonPage) {
		require_once "default.ribbon.php";
	}
?>
	<div class="page-content-wrap">
		<div class="page-content-inner">