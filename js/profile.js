var Profile = {

	showLogin: function() {
		var modal, form, child = [], fLogin, fPassword, bSubmit;

		fLogin = getField(FIELD_TYPE_TEXT_SINGLE, "login", "Логин или e-mail", "");
		fPassword = getField(FIELD_TYPE_PASSWORD, "password", "Пароль", "");
		bSubmit = ce("input", {type: "submit", value: "Вход"});

		child.push(fLogin, fPassword, bSubmit, ce("div", {"class": "auth-line-reg"}, [
			ce("a", {href: "#", onclick: function(event) {
				modal.release();
				return Profile.showRegister(event);
			}}, null, "Регистрация")
		]));

		form = ce("form", {"class": "auth-form"}, child);

		form.addEventListener("submit", function(event) {
			event.preventDefault();
			Profile.login(form.login, form.password, bSubmit, modal);
			return false;
		});

		modal = new Modal({
			title: "Авторизация",
			content: form
		});

		modal.show();
	},

	login: function(nLogin, nPassword, nButton, modal) {
		if (nButton.disabled) {
			return;
		}

		var setState = function(s) {
			nButton.disabled = !s;
			nLogin.disabled = !s;
			nPassword.disabled = !s;
		};

		setState(false);

		var login = nLogin.value.trim(),
			password = nPassword.value.trim();

		API.account.getAuthKey(login, password).then(function(res) {
			storage.set(Const.AUTH_KEY, res.authKey);

			window.mSession = new Session(res.authKey);
			window.mSession.resolve().then(Main.setSession.bind(this, window.mSession));
			modal.release();
		}).catch(function(e) {
			new Toast(Main.errors[e.error.errorId]).open(3000);
			setState(true);
		})
	},

	showRegister: function(event) {
		event.preventDefault();

		var form = ce("form", {"class": "register-form"});

		form.appendChild(getField(FIELD_TYPE_TEXT_SINGLE, "login", "Логин", ""));
		form.appendChild(getField(FIELD_TYPE_PASSWORD, "password", "Пароль", ""));
		form.appendChild(getField(FIELD_TYPE_TEXT_SINGLE, "firstName", "Имя", ""));
		form.appendChild(getField(FIELD_TYPE_TEXT_SINGLE, "lastName", "Фамилия", ""));
		form.appendChild(getField(FIELD_TYPE_RADIO, "sex", "мужской", "2"));
		form.appendChild(getField(FIELD_TYPE_RADIO, "sex", "женский", "1"));
		form.appendChild(ce("input", {type: "submit", value: "Готово"}));

		form.addEventListener("submit", function(event) {
			event.preventDefault();
			Profile.register(form, modal);
			return false;
		});

		var modal = new Modal({
			title: "Регистрация",
			content: form
		});

		modal.show();

		return false;
	},

	register: function(form, modal) {
		form.disabled = true;

		API.account.create(
			form.firstName.value.trim(),
			form.lastName.value.trim(),
			form.login.value.trim(),
			form.password.value.trim(),
			parseInt(form.sex.value.trim())
		).then(function(res) {
			modal.release();
			new Toast("Регистрация завершена, Вы id" + res.userId + ". Теперь Вы можете авторизоваться.").open(6000);
		}).catch(function(e) {
			new Toast(Main.errors[e.error.errorId]).open(3000);
			form.disabled = false;
		});
	},

	showSettings: function() {
		var tabs = new TabWrap(),
			modal = new Modal({
				title: "Настройки",
				content: tabs.getNode()
			});

		tabs.add(Profile.getProfileTab(modal))
		    .add(Profile.getPasswordTab())
		    .commit();


		modal.show();
	},

	/**
	 * @returns {Tab}
	 */
	getProfileTab: function(modal) {
		var tab = new Tab({
			name: "profile",
			title: "Профиль",
			content: getLoader()
		});

		API.users.get([]).then(function(user) {
			tab.setContent(this.createProfileForm(user, modal));
		}.bind(this));

		return tab;
	},

	/**
	 *
	 * @param {User} user
	 * @param {Modal} modal
	 * @returns {HTMLElement}
	 */
	createProfileForm: function(user, modal) {
		var form = ce("form", {"class": "x-form"});

		user = user[0];

		form.appendChild(getField(FIELD_TYPE_TEXT_SINGLE, "firstName", "Имя", user.firstName));
		form.appendChild(getField(FIELD_TYPE_TEXT_SINGLE, "lastName", "Фамилия", user.lastName));
		form.appendChild(getField(FIELD_TYPE_RADIO, "sex", "мужской", "2", {checked: user.sex === 2}));
		form.appendChild(getField(FIELD_TYPE_RADIO, "sex", "женский", "1", {checked: user.sex === 1}));
		form.appendChild(Profile.getPhotoNode());

		form.appendChild(ce("input", {type: "submit", value: "Сохранить"}));
		form.appendChild(ce("input", {type: "button", value: "Закрыть", onclick: modal.release.bind(modal)}));

		form.addEventListener("submit", Profile.saveProfileInfo.bind(form));
		return form;
	},

	saveProfileInfo: function(event) {
		event.preventDefault();

		var params = getFormParams(this);
		console.log(params);

		API.request("account.editInfo", params).then(function(result) {
			new Toast(result ? "Успешно сохранено!" : "Ошибка. Возможно. данные не были сохранены.").open(result ? 800 : 1500);
		});
	},

	getPhotoNode: function() {
		var file = ce("input", {type: "file", name: "photo", style: "display: block", accept: "image/*"});

		file.addEventListener("change", function() {
			var photo = file.files[0];
			Profile.uploadProfilePhoto(photo);
		});

		return ce("div", {"class": "x-form-row"}, [
			ce("label", {"for": "xfile"}, null, "Файл"),
			file
		]);
	},

	uploadProfilePhoto: function(photo) {
		var modal = new Modal({title: "Загрузка...", content: "0%"});
		modal.show();

		API.request("photos.upload", { type: API.photos.type.PROFILE, file: photo }).then(function(res) {
			modal.release();
			new Toast("Успешно сохранено").open(3000);
			Main.getSession().getUser().photo = new Photo(res);
			Main.showCurrentUser({session: Main.getSession()});
		}).catch(function(e) {
			console.error(e);
		});
	},

	getPasswordTab: function() {
		var tab = new Tab({name: "password", title: "Password"}),
			form = ce("form", {"class": "x-form"});

		form.appendChild(getField(FIELD_TYPE_PASSWORD, "oldPassword", "Old password", ""));
		form.appendChild(getField(FIELD_TYPE_PASSWORD, "newPassword", "New password", ""));
		form.appendChild(getField(FIELD_TYPE_PASSWORD, "newPassword", "New password", ""));

		form.appendChild(ce("input", {type: "submit", value: "Change"}));

		form.addEventListener("submit", Profile.changePassword.bind(form));

		return tab.setContent(form);
	},

	changePassword: function(event) {
		event.preventDefault();
		console.log(this)
	}

};