<?
	/** @var \Pages\BasePage $this */
	/** @var mixed $data */
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title><?=htmlSpecialChars($this->getBrowserTitle($data));?></title>
		<?=($this->hasOpenGraph() ? $this->mOpenGraphInfo : "");?>
		<?=$this->pullStyles();?>
	</head>
	<body class="<?=$this->mClassBody;?>">