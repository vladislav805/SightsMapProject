onReady(() => {

	setFormListener(ge("__userareaUserInfo"), function(event) {
		event.preventDefault();

		this["__submit"].disabled = true;

		const toast = new Toast("Подождите...");

		API.account.create(shakeOutForm(this)).then(res => {
			console.log(res);
			toast.setText("Вы зарегистрированы. Проверьте, пожалуйста, указанную почту - туда придет письмо с подтверждением.").show(10000);
		}).catch(error => {
			error = error.error;

			this["__submit"].disabled = false;

			toast.setText("Ошибка #" + error.errorId + ": " + error.message).show(4000);
		});

		return false;
	});
});
