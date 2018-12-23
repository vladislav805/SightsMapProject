<?
	/** @var \Model\User $user */
?>
<form action="#" method="post" id="__userareaChangePassword">
	<h3>Смена пароля</h3>
	<p>Пароль должен содержать от 6 до 32 символов, он регистрозависим (&laquo;kNopKa&raquo; и &laquo;knopka&raquo; &ndash; разные пароли).</p>
	<div class="singleForm-content">
		<?=new \UI\StylisedInput("oldPassword", "Старый пароль", "oldPassword");?>
		<?=new \UI\StylisedInput("newPassword", "Новый пароль", "newPassword");?>
		<?=new \UI\StylisedInput("yetPassword", "Еще раз новый пароль", "yetPassword");?>
		<div class="login-footer">
			<input value="Меняем!" type="submit" />
		</div>
	</div>
</form>

<h3>Смена фотографии профиля</h3>
<form action="#" method="post">
	<div class="singleForm-content">
		<div class="userarea-photo-current">
			<img src="<?=$user->getPhoto()->getUrlThumbnail();?>" alt="Current photo" />
		</div>
		<div class="userarea-photo-change">
			<p>Фотография должна быть не менее 720px (по большей стороне) и быть размером не более 7Мб.</p>
			<div class="manage-photos-dropZone" id="__photo-drop-zone" data-label-empty="Нажмите здесь или бростье сюда файл">
				<input type="file" id="fileElem" accept="image/*" />
			</div>
		</div>
	</div>
</form>
