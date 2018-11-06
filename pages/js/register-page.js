window.addEventListener("DOMContentLoaded", function() {
	var form = ge("__registration-form");

	form.addEventListener("submit", function(event) {
		event.preventDefault();

		API.account.create(shakeOutForm(form)).then(function(res) {
			console.log(res);
			alert("Вы зарегистрированы. Проверьте, пожалуйста, указанную почту - туда придет письмо с подтверждением.");
		}).catch(function(error) {
			error = error.error;

			alert("#" + error.errorId + ": " + error.message);
		});

		return false;
	});
});
