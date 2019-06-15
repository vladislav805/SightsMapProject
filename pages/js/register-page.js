onReady(() => {

	setFormListener(ge("__userareaUserInfo"), function(event) {
		event.preventDefault();

		this["__submit"].disabled = true;

		const toast = new Toast("Подождите...");

		const form = this;

		grecaptcha.execute(ge("gre").dataset.key, {action: "login"}).then(function(token) {
			ge("__reg_captcha").value = token;

			API.account.create(shakeOutForm(form)).then(res => {
				toast.setText("Вы зарегистрированы. Проверьте, пожалуйста, указанную почту - туда придет письмо с подтверждением.").show(10000);
			}).catch(error => {
				error = error.error;

				form["__submit"].disabled = false;

				toast.setText("Ошибка #" + error.errorId + ": " + error.message).show(4000);
			});
		});

		return false;
	});
});
