<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
		<title><?=htmlSpecialChars($this->getBrowserTitle());?></title>
		<?=($this->hasOpenGraph() ? $this->mOpenGraph->get() : "");?>
		<?=$this->pullStyles();?>
	</head>
	<body class="<?=$this->mClassBody;?>">