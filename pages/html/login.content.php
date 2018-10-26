<div class="login-wrap">
	<form action="/login?action=authorize" method="post">
		<div class="login-logo">
			<i class="material-icons">&#xe55b;</i>
			<div class="login-logo-label">Sights Map</div>
		</div>
		<?=new \UI\StylisedInput("login", "Логин или e-mail");?>
		<?=(new \UI\StylisedInput("password", "Пароль"))->setType("password");?>
		<div class="login-footer">
			<input value="Вход" type="submit" />
			<div><a class="button" href="/user/registration">Регистрация</a></div>
		</div>
	</form>
</div>