<div id="head">
	<a id="head-logo" href="/index">
		<i class="material-icons">&#xe55b;</i>
	</a>

	<div id="head-user">
<? if ($this->mController->getSession()) { ?>
		<div id="head-events" data-count="0" class="head-events material-icons head-element">&#xe7f4;</div>
<? } ?>
		<div class="head-user head-element">
<?
	if ($this->mController->getSession()) {
		/** @var \Model\User $u */
		$u = $this->mController->getUser();
?>
			<img class="head-user-photo" id="hatPhoto" src="<?=htmlspecialchars($u->getPhoto()->getUrlThumbnail());?>" alt="" />
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
<?
	if ($this instanceof \Pages\RibbonPage) {
?>
<div class="page-ribbon" style="background: url('<?=$this->getRibbonImage();?>') no-repeat center center; background-size: cover;">
<?
	if ($content = $this->getRibbonContent()) {
?>
<h1><?=htmlSpecialChars($content);?></h1>
<?
	}
?>
</div>
<?
	}
?>
<div class="page-content">
	<div class="page-content-wrap">
		<div class="page-content-inner">