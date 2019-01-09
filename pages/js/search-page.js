var Search = {

	onFormSubmit: function(event) {
		event.preventDefault();

		const params = shakeOutForm(this);

		for (let key in params) {
			if (params.hasOwnProperty(key) && (!params[key] || params[key] === "0")) {
				delete params[key];
			}
		}


		navigateTo("/sight/search?" + Sugar.Object.toQueryString(params), null);

		return false;
	},

	showCities: function(form) {
		const setValue = city => {
			ge("searchView_city").textContent = city ? city.name : "";
			ge("search_cityId").value = city ? city.cityId : 0;
		};
		const conf = smartModalsExtendConfiguration(SMART_CONFIGURATION_CITIES, {
			getButtons() {
				return [{
					name: "ok",
					label: "Готово",
					onClick: function (name, callbacks, modal) {
						const city = callbacks.getData();
						setValue(city);
						modal.release();
					}
				}, {
					name: "reset",
					label: "Сбросить",
					onClick: function (name, callbacks, modal) {
						setValue(null);
						modal.release();
					}
				}, {
					name: "cancel",
					label: "Отмена",
					onClick: function (name, callbacks, modal) {
						modal.release();
					}
				}];
			}
		});

		showSmartModal(conf, {selected: [Number(ge("search_cityId").value)]});
	},

	showMarks: function(form) {
		const setValue = marks => {
			ge("searchView_marks").textContent = marks && marks.length ? marks.map(mark => mark.title).join(", ") : "";
			ge("search_markIds").value = marks && marks.length ? marks.map(mark => mark.markId).join(",") : "";
		};
		const conf = smartModalsExtendConfiguration(SMART_CONFIGURATION_MARKS, {
			getButtons() {
				return [{
					name: "ok",
					label: "Готово",
					onClick: function(name, callbacks, modal) {
						const marks = callbacks.getData();
						setValue(marks);
						modal.release();
					}
				}, {
					name: "reset",
					label: "Сбросить",
					onClick: function(name, callbacks, modal) {
						setValue(null);
						modal.release();
					}
				}, {
					name: "cancel",
					label: "Отмена",
					onClick: function(name, callbacks, modal) {
						modal.release();
					}
				}]
			}
		});

		const current = ge("search_markIds").value.split(",").map(id => Number(id)).filter(i => !isNaN(i) && i);

		showSmartModal(conf, {selected: current});
	},

	init: function() {
		const form = ge("search-main-form");

		form.addEventListener("submit", event => Search.onFormSubmit.call(form, event));
	}

};