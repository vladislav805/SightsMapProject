<?
	/** @var array $data */
?>
<h1>Сопряжение профиля с аккаунтом Telegram</h1>
<p>Ниже предоставлен временный код, который нужно отправть боту:</p>
<div class="telegram-code bg-papers">
	<pre class="bg-papers-content"><?=$data["code"];?></pre>
</div>
<p>Этот код нужно ввести после команды /auth в Telegram. Он действителен в течение <?=getRelativeDate($data["expiredIn"]);?></p>
<img class="telegram-sendExample" src="/images/telegram-auth.jpg" alt="Пример" />
<h4>Для чего это может быть нужно?</h4>
<p>С мобильного телефона намного удобнее получать информацию через мессенджеры. Поэтому мы уместили все, как нам кажется, функции, которые могут понадобиться при прогулке по городу :)</p>