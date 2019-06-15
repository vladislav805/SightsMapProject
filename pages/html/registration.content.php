<?
	/** @var \Model\User|null $user */
	/** @var array $cities */

	use Model\User;
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
				<option <?=$user === null ? "selected" : "";?> value="<?=User::GENDER_NOT_SET;?>" disabled hidden>не выбрано</option>
				<option <?=$user && $user->getSex() === User::GENDER_FEMALE ? "selected" : "";?> value="<?=User::GENDER_FEMALE;?>">женский</option>
				<option <?=$user && $user->getSex() === User::GENDER_MALE ? "selected" : "";?> value="<?=User::GENDER_MALE;?>">мужской</option>
			</select>
			<label for="m-sex">Пол</label>
		</div>
		<?=new StylisedSelect("cityId", "Город", $cities);?>
	</div>
<?
	if ($user === null) {
?>
<script src="https://www.google.com/recaptcha/api.js?render=<?=GOOGLE_RECAPTCHA_PUBLIC_TOKEN;?>" id="gre" data-key="<?=GOOGLE_RECAPTCHA_PUBLIC_TOKEN;?>"></script>
	<input type="hidden" name="captchaId" value="" id="__reg_captcha" />
<script>
	grecaptcha.ready(function() {

	});
</script>
		<p>При нажатии на кнопку &laquo;Регистрация&raquo; пользователь принимает <a href="/policy-privacy.html" target="_blank" data-noAjax>политику обработки персональных данных</a></p>
<?
	}
?>
	<div class="singleForm-footer">
		<input value="<?=$user ? "Сохранить" : "Регистрация";?>" name="__submit" type="submit" />
	</div>
</form>
