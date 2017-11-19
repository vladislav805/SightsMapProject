var Comments = {

	/**
	 *
	 * @param {Point} point
	 * @returns {HTMLElement}
	 */
	getWidget: function(point) {
		var head,
			loader,
			items = ce("div", {"class": "comments-items", "data-comments-for": point.getId()}, [loader = getLoader()]),
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
	 * @param {int} pointId
	 * @returns {Promise.<Comment[]>}
	 */
	request: function(pointId) {
		return API.comments.get(pointId).then(function(d) {
			d.users.forEach(User.get);

			var list =  d.items.map(function(f) {
				f["author"] = User.sCache.get(f.userId);
				return new Comment(f);
			});

			Main.fire(EventCode.COMMENT_LIST_LOADED, {pointId: pointId, count: d.count, items: list});

			return list;
		}).catch(console.error.bind(console));
	},

	/**
	 *
	 * @param {function} onSubmit
	 */
	getForm: function(onSubmit) {
		return ce("form", {"class": "comment-form __user-authorized", onsubmit: onSubmit}, [
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
			res = new Comment(res);
			Main.fire(EventCode.COMMENT_ADDED, {point: point, comment: res});
		});

		return false;
	},

	/**
	 *
	 * @param {Comment} comment
	 * @param {{point: Point}} options
	 */
	getItem: function(comment, options) {
		var u = comment.author || User.sCache.get(comment.getUserId());
		console.log(u, comment.getUserId());
		return ce("div", {"class": "comment-item", "data-comment-id": comment.getId()}, [
			ce("div", {"class": "comment-author-photo"}, [
				ce("img", {src: u.getPhoto().get(Photo.size.THUMBNAIL)})
			]),
			ce("div", {"class": "comment-content"}, [
				ce("a", {"class": "comment-author-name", href: "./user/" + u.getLogin()}, null, u.getFullName().safetyHTML()),
				ce("div", {"class": "comment-text"}, null, comment.getText().safetyHTML()),
				ce("div", {"class": "comment-footer"}, [
					ce("time", {"class": "comment-date"}, null, comment.getDate().format(Const.DEFAULT_FULL_DATE_FORMAT)),
					comment.getCanModify()
						? " | "
						: "",
					comment.getCanModify()
						? ce("span", {"class": "a", onclick: function() {
							Comments.removeComment(options.point, comment);
						}}, null, "Удалить")
						: ""
				])
			])
		]);
	},

	/**
	 *
	 * @param {Point} point
	 * @param {Comment} comment
	 */
	removeComment: function(point, comment) {
		xConfirm("Подтверждение", "Вы действительно хотите удалить комментарий?", "Да", "Отмена").then(function() {
			API.comments.remove(comment.getId()).then(function() {
				Main.fire(EventCode.COMMENT_REMOVED, {point: point, comment: comment});
			});
		});
	},

	event: {

		/**
		 *
		 * @param {{point: Point, comment: Comment}} args
		 */
		onAdded: function(args) {
			var wrapItems = document.querySelector("[data-comments-for='" + args.point.getId() + "']");

			if (!wrapItems) {
				return;
			}

			wrapItems.appendChild(Comments.getItem(args.comment, {point: args.point}));
		},

		/**
		 *
		 * @param {{point: Point, comment: Comment}} args
		 */
		onRemove: function(args) {
			Array.prototype.forEach.call(document.querySelectorAll("[data-comment-id='" + args.comment.getId() + "']"), function(d) {
				d.parentNode.removeChild(d);
			});
			new Toast("Удалено").open(3000);
		}

	}

};