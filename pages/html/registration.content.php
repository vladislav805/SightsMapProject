<?
	/** @var \Model\User|null $user */
	/** @var array $cities */

	use UI\{StylisedInput, StylisedSelect};

?>
<form action="#" method="post" id="__userareaUserInfo">
<?
	if ($user === null) {
?>
	<h3>Регистрация пользователя</h3>
	<p>Регистрация пользователя дает возможность редактировать карту достопримечательностей, общаться с другими пользователями и вести список посещенных/желаемых к посещению мест, а также доступ к нашему нейронному помощнику.</p>
<?
	} else {
?>
	<h3>Основная информация</h3>
	<p>Желательно указывать здесь свои реальные имя и фамилию. Ничего плохого не будет, если тут будет выдумка, но всё-таки так будет лучше.</p>
<?
	}
?>

	<div class="singleForm-content">
		<?=$user === null ? new StylisedInput("email", "E-mail") : "";?>
		<?=$user === null ? new StylisedInput("login", "Логин") : "";?>
		<?=$user === null ? (new StylisedInput("password", "Пароль"))->setType("password") : "";?>
		<?=new StylisedInput("firstName", "Имя", null, $user ? $user->getFirstName() : "");?>
		<?=new StylisedInput("lastName", "Фамилия", null, $user ? $user->getLastName() : "");?>

		<div class="fi-wrap">
			<select name="sex" required id="m-sex">
				<option <?=$user === null ? "selected" : "";?> disabled hidden>не выбрано</option>
				<option <?=$user && $user->getSex() === 1 ? "selected" : "";?> value="1">женский</option>
				<option <?=$user && $user->getSex() === 2 ? "selected" : "";?> value="2">мужской</option>
			</select>
			<label for="m-sex">Пол</label>
		</div>
		<?=new StylisedSelect("cityId", "Город", $cities);?>
	</div>
	<div class="login-footer">
		<input value="<?=$user ? "Сохранить" : "Регистрация";?>" name="__submit" type="submit" />
	</div>
</form>
