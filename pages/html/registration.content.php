<form action="#" method="post" id="__registration-form">
	<h1>Регистрация пользователя</h1>
	<p>Регистрация пользователя дает возможность редактировать карту достопримечательностей, общаться с другими пользователями и вести собственный список итересных мест</p>

	<div class="registration-content-form">
		<?=new \UI\StylisedInput("email", "E-mail");?>
		<?=new \UI\StylisedInput("login", "Логин");?>
		<?=(new \UI\StylisedInput("password", "Пароль"))->setType("password");?>
		<?=new \UI\StylisedInput("firstName", "Имя");?>
		<?=new \UI\StylisedInput("lastName", "Фамилия");?>
		<label for="sex">Пол</label>
		<select name="sex" required id="sex">
			<option selected disabled hidden>не выбрано</option>
			<option value="1">женский</option>
			<option value="2">мужской</option>
		</select>
	</div>
	<div class="login-footer">
		<input value="Регистрация" type="submit" />
	</div>
</form>
