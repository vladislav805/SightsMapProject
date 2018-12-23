onReady(() => {

	setFormListener(ge("__userareaUserInfo"), function(event) {
		event.preventDefault();

		API.account.create(shakeOutForm(this)).then(res => {
			console.log(res);
			alert("Вы зарегистрированы. Проверьте, пожалуйста, указанную почту - туда придет письмо с подтверждением.");
		}).catch(error => {
			error = error.error;

			alert("#" + error.errorId + ": " + error.message);
		});

		return false;
	});
});
