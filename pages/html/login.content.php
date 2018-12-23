<div class="login-wrap">
	<form action="/login?action=authorize" method="post">
		<input type="hidden" name="repath" value="<?=htmlSpecialChars(get("repath"));?>" />
		<div class="login-logo">
			<i class="material-icons">&#xe55b;</i>
			<div class="login-logo-label">Sights Map</div>
		</div>
		<?=new \UI\StylisedInput("login", "Логин или e-mail");?>
		<?=(new \UI\StylisedInput("password", "Пароль"))->setType("password");?>
		<div class="login-footer">
			<div><input value="Вход" type="submit" /> <a class="button" href="/userarea/create">Регистрация</a></div>
			<div><a class="button" href="/userarea/vk">Через ВКонтакте</a></div>
		</div>
	</form>
</div>