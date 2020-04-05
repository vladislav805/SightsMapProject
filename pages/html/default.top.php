<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
?>
<!doctype html>
<html lang="ru">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title><?=htmlSpecialChars($this->getBrowserTitle($data));?></title>
		<meta name="yandex-verification" content="0a042fa3e5d037a7" />
		<link rel="dns-prefetch" href="//<?=DOMAIN_MEDIA;?>">
		<link rel="icon" href="/favicon.png" type="image/x-icon" />
		<?=$this->getCanonicalLink();?>
		<?=($this->hasOpenGraph() ? $this->getOpenGraph() : "");?>
		<?=$this->pullStyles();?>
	</head>
	<body class="<?=join(" ", $this->mClassBody);?>">
		<div class="loader-wrap">
			<div class="loader"><div></div><div></div><div></div></div>
		</div>