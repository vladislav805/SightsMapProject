var Comments = {

	/**
	 *
	 * @param {Point} point
	 * @returns {HTMLElement}
	 */
	getWidget: function(point) {
		var head,
			loader,
			items = ce("div", {"class": "comments-items"}, [loader = getLoader()]),
			form = this.getForm(function(event) {
				return this.sendComment(form, point, event)
			}.bind(this));

		head = ce("div", {"class": "comments-widget-wrap"}, [
			ce("h3", null, [
				getIcon("e0b9"),
				"Комментарии"
			])
		]);

		form.hidden = true;

		this.request(point.getId()).then(function(data) {
			items.removeChild(loader);

			point.setPhotos(data);

			items.dataset.emptyLabel = "Нет комментариев";

			if (!data.length) {
				return;
			}

			data.forEach(function(comment) {
				items.appendChild(Comments.getItem(comment, {point: point}));
			});
			form.hidden = false;
		}).catch(function(e) {
			items.removeChild(loader);

			items.dataset.emptyLabel = "Что-то пошло не так... Ошибка:\n\n" + JSON.stringify(e);
			console.error(e);
		});

		return ce("div", {"class": "comments-widget"}, [head, items, form]);
	},

	/**
	 * Получение данных о комментариях под меткой
	 * @param pointId
	 * @returns {Promise.<Comment[]>}
	 */
	request: function(pointId) {
		return API.comments.get(pointId).then(function(d) {
			return d.items.map(function(f) {
				return new Comment(f);
			});
		});
	},

	/**
	 *
	 * @param {function} onSubmit
	 */
	getForm: function(onSubmit) {
		return ce("form", {"class": "comment-form", onsubmit: onSubmit}, [
			ce("textarea", {name: "text", placeholder: "Комментировать..."}),
			ce("input", {type: "submit", value: "\uE163"})
		]);
	},

	/**
	 *
	 * @param {HTMLElement} form
	 * @param {Point} point
	 * @param {Event} event
	 */
	sendComment: function(form, point, event) {
		event.preventDefault();

		var text = getValue(form["text"]);

		if (!text) {
			return false;
		}

		API.comments.add(point.getId(), text).then(function(res) {
			console.log("COMMENT POSTED", res);
		})

		return false;
	},

	/**
	 *
	 * @param {Comment} comment
	 */
	getItem: function(comment) {
		return ce("div", {"class": "comment-item"}, null, comment.getText());
	}

};