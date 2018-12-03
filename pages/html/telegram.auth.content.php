<?
	/** @var array $data */
?>
<h1><?=$data["code"];?></h1>
<p>Этот код нужно ввести после команды /auth в Telegram. Он действителен в течение <?=getRelativeDate($data["expiredIn"]);?></p>