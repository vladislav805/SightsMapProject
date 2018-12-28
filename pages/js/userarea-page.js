"use strict";

const UserArea = {

	/**
	 *
	 * @param {Event} event
	 */
	onEditInfoFormSubmit: function(event) {
		event.preventDefault();

		const toast = new Toast();

		API.account.editInfo(shakeOutForm(this)).then(result => {
			toast.setText("Успешно сохранено").show(3000);
		}).catch(error => {
			error = error.error;

			toast.setText("#" + error.errorId + ": " + error.message).show(3000);
		});

		return false;
	},

	/**
	 *
	 * @param {Event} event
	 */
	onChangePasswordSubmit: function(event) {
		event.preventDefault();

		/** @var {{oldPassword: string, newPassword: string, yetPassword: string}} params */
		let params = shakeOutForm(this);

		const toast = new Toast();
		let error = null;

		if (params.newPassword !== params.yetPassword) {
			error = "Повтор не совпадает с новым паролем";
		}

		if (params.newPassword.length < 6 || params.newPassword > 32) {
			error = "Пароль должен быть длиной от 6 до 32 символов";
		}

		if (error) {
			toast.setText(error).show(4000);
			return false;
		}

		API.account.changePassword(params.oldPassword, params.newPassword).then(result => {
			console.log(result);
			toast.setText("Пароль изменен. Все сессии, кроме текущей, были сброшены.").show(5000);
		}).catch(error => {
			console.log(error);
			let e = error.error;
			let msg;
			switch (e.errorId) {
				case API.error.INCORRECT_LOGIN_PASSWORD: msg = "Неверный пароль"; break;
				case API.error.INCORRECT_LENGTH_PASSWORD: msg = "Неверная длина пароля"; break;
			}
			msg && toast.setText(msg).show(5000);
		});


		return false;
	},

	/**
	 *
	 * @param {Event} event
	 */
	onPhotoFormSubmit: function(event) {
		event.preventDefault();

		return false;
	}
};

onReady(() => {
	setFormListener(ge("__userareaUserInfo"), UserArea.onEditInfoFormSubmit);
	setFormListener(ge("__userareaChangePassword"), UserArea.onChangePasswordSubmit);
	setFormListener(ge("__userareaUpdatePhoto"), UserArea.onPhotoFormSubmit);
});