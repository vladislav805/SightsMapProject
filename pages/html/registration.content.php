<?
	/** @var \Model\User|null $user */
	/** @var array $cities */
?>
<form action="#" method="post" id="__registration-form">
<?
	if ($user === null) {
?>
	<h3>Регистрация пользователя</h3>
	<p>Регистрация пользователя дает возможность редактировать карту достопримечательностей, общаться с другими пользователями и вести собственный список итересных мест</p>
<?
	} else {
?>
	<h3>Основная информация</h3>
	<p>Желательно указывать здесь свои реальные имя и фамилию. Ничего плохого не будет, если тут будет выдумка, но всё-таки так будет лучше.</p>
<?
	}
?>

	<div class="singleForm-content">
		<?=$user === null ? new \UI\StylisedInput("email", "E-mail") : "";?>
		<?=new \UI\StylisedInput("login", "Логин", null, $user ? $user->getLogin() : "");?>
		<?=$user === null ? (new \UI\StylisedInput("password", "Пароль"))->setType("password") : "";?>
		<?=new \UI\StylisedInput("firstName", "Имя", null, $user ? $user->getFirstName() : "");?>
		<?=new \UI\StylisedInput("lastName", "Фамилия", null, $user ? $user->getLastName() : "");?>

		<div class="fi-wrap">
			<select name="sex" required id="m-sex">
				<option <?=$user === null ? "selected" : "";?> disabled hidden>не выбрано</option>
				<option <?=$user && $user->getSex() === 1 ? "selected" : "";?> value="1">женский</option>
				<option <?=$user && $user->getSex() === 2 ? "selected" : "";?> value="2">мужской</option>
			</select>
			<label for="m-sex">Пол</label>
		</div>
		<?=new \UI\StylisedSelect("cityId", "Город", $cities);?>
	</div>
	<div class="login-footer">
		<input value="<?=$user ? "Сохранить" : "Регистрация";?>" type="submit" />
	</div>
</form>
