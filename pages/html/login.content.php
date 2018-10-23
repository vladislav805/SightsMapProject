<div class="login-wrap">
	<form action="/login?action=authorize" method="post">
		<div class="login-logo">
			<i class="material-icons">&#xe55b;</i>
			<div class="login-logo-label">Sights Map</div>
		</div>
		<div class="fi-wrap">
			<input type="text" name="login" id="m-login" pattern=".+" required="required" />
			<label for="m-login">Логин или e-mail</label>
		</div>
		<div class="fi-wrap">
			<input type="password" name="password" id="m-password" pattern=".+" required="required" />
			<label for="m-password">Пароль</label>
		</div>
		<div class="login-footer">
			<input value="Вход" type="submit" />
		</div>
	</form>
</div>