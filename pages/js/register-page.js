onReady(() => {

	setFormListener(ge("__userareaUserInfo"), function(event) {
		event.preventDefault();

		const toast = new Toast();

		API.account.create(shakeOutForm(this)).then(res => {
			console.log(res);
			toast.setText("Вы зарегистрированы. Проверьте, пожалуйста, указанную почту - туда придет письмо с подтверждением.").show(10000);
		}).catch(error => {
			error = error.error;

			toast.setText("Ошибка #" + error.errorId + ": " + error.message).show(4000);
		});

		return false;
	});
});
