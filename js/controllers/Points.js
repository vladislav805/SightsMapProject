var Points = {

	CLASS_INFO_VERIFIED: "info-point-verified",

	/**
	 * Внешний
	 * @var {HTMLElement}
	 */
	mPointsWrapper: null,

	/**
	 * Именно список
	 * @var {HTMLElement}
	 */
	mPointsList: null,

	/**
	 * Инициализация бокового меню
	 * Находим меню, создаем обертку вкладок, вставляем в меню
	 */
	init: function() {
		this.mPointsWrapper = g("list");
		this.mPointsWrapper.appendChild(this.mPointsList = ce("div", {id: "asideListPoint"}, getLoader()));
	},

	/**
	 * Обновление списка по новым данным
	 * @param {{count: int, items: Place[]}} data
	 */
	showList: function(data) {
		var f = this.mPointsList;
		while (f.firstChild) {
			f.removeChild(f.firstChild);
		}

		data.items.forEach(function(item) {
			this.mPointsList.appendChild(item.getListItemNode());
		}, this);
	},














	/**
	 *
	 * @param {Point} p
	 * @returns {{node: (Node|HTMLElement), args: {pointId: int}}}
	 */
	getInfoWidget: function(p) {
		var title = ce("h1", null, null, p.getTitle().escapeHTML()),
			description = ce("p", null, null, p.getDescription().escapeHTML()),

			dateCreated = ce("p", {"class": "info-date"}, null, "Создано " + p.dateCreated.format(Const.DEFAULT_FULL_DATE_FORMAT)),
			dateUpdated = p.dateUpdated ? ce("p", {"class": "info-date"}, null, "Отредактировано " + p.dateUpdated.format(Const.DEFAULT_FULL_DATE_FORMAT)) : null,

			marks = ce("div", {"class": "info-marks-items"}, Points.getMarksViewWidget(p)),

			author = ce("p", {"class": "info-author"}, [
				ce("span", null, null, "Автор: "),
				ce("a", {href: "user/" + p.mAuthor.getLogin()}, null, "@" + p.mAuthor.getLogin() + " (" + p.mAuthor.getFullName() + ")")
			]),

			actions = ce("div", {"class": "info-actions"}, this.getActions(p)),

			classes = ["info-point-wrap"];
		p.isVerified && classes.push(Points.CLASS_INFO_VERIFIED);

		return {
			node: ce("div", {"class": classes.join(" ")}, [
				title,
				description,
				marks,
				dateCreated,
				dateUpdated,
				author,
				actions,
				ce("hr"),
				Photos.getWidget(p)
			]),
			args: {
				pointId: p.getId()
			}
		};
	},

	/**
	 * Возвращает массив из кнопок, функции которых доступны для текущего пользователя над меткой
	 * @param {Point} p
	 * @returns {HTMLElement[]}
	 */
	getActions: function(p) {
		var items = [],
			item = function(code, id, label, onClick) {
				return ce("div", {"class": "point-action", "data-action-id": id, onclick: onClick}, [getIcon(code), label]);
			};

		items.push(this.getVisitStateSwitcher(p));
		items.push(item("E89e", "link", "Скопировать ссылку", Points.copyLink.bind(this, p)));

		if (p.canModify) {
			items.push(item("e89f", "move", "Переместить", Points.makePointMovable.bind(this, p)));
			items.push(item("e150", "edit", "Редактировать", Points.showEditForm.bind(this, p)));
			items.push(item("e872", "remove", "Удалить", Points.removeConfirmWindow.bind(this, p)));
		}


		return items;
	},

	/**
	 * Копирует ссылку в буфер обмена, сгенерированной по данным о метке
	 * @param {Point} point
	 */
	copyLink: function(point) {
		new Toast(copy2clipboard(point.getLink()) ? "Ссылка успешно скопирована" : "Что-то пошло не так.. Возможно, у вас старый браузер").open(1000);
	},

	/**
	 *
	 * @param {Point} p
	 * @returns {Node|HTMLElement}
	 */
	getVisitStateSwitcher: function(p) {
		var wrap,
			setState = function(state) {
				API.points.setVisitState(p.getId(), state).then(function() {
					wrap.dataset.visitState = state;
					p.visitState = state;
				});
			},
			onClick = function(state) {
				return setState.bind(Points, state);
			},
			buttons = [
				ce("div", {
					"class": "point-action",
					"data-action-id": "visit-0",
					onclick: onClick(Point.visitState.NOT_VISITED)
				}, [getIcon("e14c"), "Не посещено"]),
				ce("div", {
					"class": "point-action",
					"data-action-id": "visit-1",
					onclick: onClick(Point.visitState.VISITED)
				}, [getIcon("e876"), "Посещено"]),
				ce("div", {
					"class": "point-action",
					"data-action-id": "visit-2",
					onclick: onClick(Point.visitState.DESIRED)
				}, [getIcon("e566"), "Хочу сюда"])
			];
		return wrap = ce("div", {"class": "point-visit-state", "data-visit-state": p.getVisitState()}, buttons);
	},

	/**
	 *
	 * @param {Point} p
	 */
	getMarksViewWidget: function(p) {
		return p.getMarkIds().map(function(i) {
			/** @var {Mark} */
			var mark = Marks.getBundle().get(i);
			return ce("div", {"class": "mark-item", "data-mark-id": i, style: "background: #" + PlacemarkIcon.getHEX(mark.getColor())}, mark.getTitle());
		});
	},

	/**
	 * Открывает и показывает форму
	 * @param {Point} point
	 */
	showEditForm: function(point) {
		var isNew = !point.getId(),
			form,
			modal = new Modal({
				title: isNew ? "Новая метка" : "Редактирование метки",
				content: form = ce("form", {}, [
					getField(FIELD_TYPE_TEXT_SINGLE, "title", "Название", point.getTitle()),
					getField(FIELD_TYPE_TEXT, "description", "Описание", point.getDescription()),
					ce("input", {type: "submit", value: "Сохранить"})
				])
			});
		modal.show();

		form.addEventListener("submit", Points.onSubmitEditOrCreate.bind(form, point, modal));
	},

	/**
	 *
	 * @param point
	 */
	makePointMovable: function(point) {
		Main.fire(EventCode.POINT_MOVE, {point: point});

		/**
		 * @param {{lat: float, lng: float, point: Point}} args
		 */
		var listener = function(args) {
			args.point.lat = args.lat;
			args.point.lng = args.lng;
			toast.setText("Сохранение...");
			API.points.move(args.point.getId(), args.lat, args.lng).then(function() {
				toast.setText("Сохранено").open(2000);
			});
			Main.removeListener(EventCode.POINT_MOVED, listener);
		}, toast;

		Main.addListener(EventCode.POINT_MOVED, listener);

		toast = new Toast("Переместите метку в нужное место").open(Infinity);
	},

	/**
	 * Открывает подтверждение и, в случае положительного ответа, удаляет место
	 * @param {Point} point
	 */
	removeConfirmWindow: function(point) {
		var confirmed = function() {
				toast.setButtons([]).setText("Removing...");
				API.points.remove(point.getId()).then(function(result) {
					if (result) {
						toast.setText("Successfully removed!").open(1000);
						Main.fire(Const.POINT_REMOVED, {point: point});
					}
				});
			},
			rejected = function() {
				toast.close();
			},
			toast = new Toast("Вы уверены, что хотите удалить это место?", {
				buttons: [
					{ label: "Удалить", onclick: confirmed },
					{ label: "Отмена", onclick: rejected }
				]
			}).open(15000);
	},

	/**
	 *
	 * @param {Point} point
	 * @param {Modal} modal
	 * @param {Event} event
	 * @returns {boolean}
	 */
	onSubmitEditOrCreate: function(point, modal, event) {
		event.preventDefault();

		var t = getValue(this.title), d = getValue(this.description);

		(!point.getId()
			? API.points.add({title: t, description: d, lat: point.getLat(), lng: point.getLng()})
			: API.points.edit(point.getId(), {title: t, description: d})
		).then(function(result) {
			new Toast("Успшено сохранено, спасибо!").open(4000);
			modal.release();
			point.title = result.title;
			point.description = result.description;

			Main.fire(point.getId() ? EventCode.POINT_EDITED : EventCode.POINT_CREATED, point.getId() ? point : new Place(result));
		});

		return false;
	}

};