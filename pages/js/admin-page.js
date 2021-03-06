(function(api) {
	api.admin = {
		setBan: function(userId, reason, comment) {
			return api.request("admin.setBan", {userId: userId, reason: reason, comment: comment || ""});
		},

		setUserJob: function(userId, status) {
			return api.request("admin.setUserJob", {userId: userId, status: status});
		}
	};
})(API);

const Admin = {

	initBanPage: function() {
		ge("__adminBanAdd").addEventListener("submit", function(e) {
			return Admin.banUser(e, this);
		});
	},

	initJobsPage: function() {
		ge("__adminJobSet").addEventListener("submit", function(e) {
			return Admin.setJob(e, this);
		});
	},

	initCitiesPage: function() {
		ge("__adminCityAdd").addEventListener("submit", function(e) {
			return Admin.addCity(e, this);
		});

		const click = function(e) {
			const currText = e.textContent;
			const newText = prompt("Новое значение", currText);

			if (!newText) {
				return;
			}

			const row = e.parentNode;

			// TODO make foreach
			const data = {
				name: row.querySelector("[data-key='name']").textContent,
				parentId: row.querySelector("[data-key='parentId']").textContent,
				lat: row.querySelector("[data-key='lat']").textContent,
				lng: row.querySelector("[data-key='lng']").textContent,
				radius: row.querySelector("[data-key='radius']").textContent,
				description: row.querySelector("[data-key='description']").textContent,
			};

			e.textContent = newText;

			API.cities.edit(row.dataset.cityId, data);
		};

		Array.from(document.querySelectorAll(".admin-city-editable")).map(element => {
			element.addEventListener("click", click.bind(null, element));
		});
	},

	banUser: function(event, form) {
		event && event.preventDefault();

		const data = shakeOutForm(form);

		API.admin.setBan(data.userId, data.reason, data.comment).then(res => {
			refreshCurrent();
		});

		return false;
	},

	unbanUser: function(node) {
		const userId = +node.dataset.uid;

		API.admin.setBan(userId, 0).then(() => {
			Array.from(document.querySelectorAll(".admin-banned-user-" + userId)).forEach(item => {
				item.parentNode.removeChild(item);
			});
		});
	},

	setJob: function(event, form) {
		event && event.preventDefault();

		const data = shakeOutForm(form);

		API.admin.setUserJob(data.userId, data.status).then(res => {
			refreshCurrent();
		});

		return false;
	},

	focusSetJob: function(node) {
		const form = ge("__adminJobSet");
		const opts = Array.from(form.elements["status"].options).map(node => node.value);

		form.parentNode.classList.add("spoiler--open");
		form.elements["userId"].value = node.dataset.userId;
		form.elements["status"].selectedIndex = opts.indexOf(node.dataset.userStatus);
	},

	addCity: function(event, form) {
		event && event.preventDefault();

		const data = shakeOutForm(form);

		API.cities.add(data.name, data.parentId, data.lat, data.lng, data.radius, data.description).then(res => {
			refreshCurrent();
		});

		return false;
	},

	removeCity: function(node) {
		const row = node.parentNode.parentNode;
		const cityId = +row.dataset.cityId;
		if (!confirm("Подтверждение. Удалить город?")) {
			return;
		}
		API.cities.remove(cityId).then(function(res) {
			row.parentNode.removeChild(row);
		});
	}
};