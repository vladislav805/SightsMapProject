"use strict";

const UserArea = {

	/**
	 *
	 * @param {Event} event
	 */
	onEditInfoFormSubmit: function(event) {
		event.preventDefault();

		API.account.editInfo(shakeOutForm(this)).then(result => {
			console.log(result);
		}).catch(error => {
			error = error.error;

			alert("#" + error.errorId + ": " + error.message);
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

		if (params.newPassword !== params.yetPassword) {
			alert("Повтор не совпадает с новым паролем");
			return false;
		}

		if (params.newPassword.length < 6 || params.newPassword > 32) {
			alert("Пароль должен быть длиной от 6 до 32 символов");
			return false;
		}

		API.account.changePassword(params.oldPassword, params.newPassword).then(result => {
			console.log(result);
			alert("Успешно");
		}).catch(error => {
			console.log(error);
			let e = error.error;
			let msg;
			switch (e.errorId) {
				case API.error.INCORRECT_LOGIN_PASSWORD: msg = "Неверный пароль"; break;
				case API.error.INCORRECT_LENGTH_PASSWORD: msg = "Неверная длина пароля"; break;
			}
			msg && alert(msg);
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