const Docs = {
	runMethod: function(method, form, event) {
		event && event.preventDefault();

		const params = new FormData();

		const shake = shakeOutForm(form);
console.log(shake);
		for (let key in shake) {
			if (shake.hasOwnProperty(key) && shake[key]) {
				params.append(key, shake[key]);
			}
		}

		fetch("/api.php?method=" + method, {
			method: "POST",
			body: params
		}).then(res => res.text()).then(res => {
			if (res.indexOf("{") === 0) {
				this.setTextResult(JSON.stringify(JSON.parse(res), null, "\t"));
			} else {
				this.setTextResult(res);
			}
		}).catch(re => {
			console.log(re);
			this.setTextResult(re);
		});

		return false;
	},

	setTextResult: function(res) {
		ge("docs-result").innerHTML = res;
	}
};