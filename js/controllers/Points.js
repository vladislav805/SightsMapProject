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
	 * @param {{count: int, items: Point[]}} items
	 */
	showList: function(items) {
		items.items.forEach(function(item) {
			this.mPointsList.appendChild(this.getItem(item));
		}, this);
	},

	/**
	 * Создает и возвращает элемент списка точки
	 * @param {Point} point
	 * @returns {Node|HTMLElement}
	 */
	getItem: function(point) {
		var nodeTitle, nodeSubtitle, node = ce("div",{"class": "listItem"}, [
			nodeTitle = ce("div", {"class": "listItem-title"}, null, point.getTitle().escapeHTML()),
			nodeSubtitle = ce("div", {"class": "listItem-subtitle"}, null, "asd")
		]);

		node.addEventListener("click", Points.findAndShowPoint.bind(Points, point));

		return node;
	},

	findAndShowPoint: function(point) {
		//
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

		items.push(item("E89e", "link", "Скопировать ссылку", Points.copyLink.bind(this, p)));
		items.push(this.getVisitStateSwitcher(p));

		if (p.canModify) {
			items.push(item("e89f", "move", "Переместить"));
			items.push(item("e150", "edit", "Редактировать", Points.showEditForm.bind(this, p)));
			items.push(item("e872", "remove", "Удалить"));
		}


		return items;
	},

	/**
	 * Копирует ссылку в буфер обмена, сгенерированной по данным о метке
	 * @param {Point} point
	 */
	copyLink: function(point) {
		new Toast(copy2clipboard(point.getLink()) ? "Ссылка успешно скоирована" : "Что-то пошло не так.. Возможно, у вас старый браузер").open(1000);
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

		form.addEventListener("submit", Points.onSubmitEditOrCreate.bind(form, point));
	},

	/**
	 *
	 * @param {Point} point
	 * @param {Event} event
	 * @returns {boolean}
	 */
	onSubmitEditOrCreate: function(point, event) {
		event.preventDefault();

		var t = getValue(this.title), d = getValue(this.description);

		(!point.getId()
			? API.points.add({title: t, description: d, lat: point.getLat(), lng: point.getLng()})
			: API.points.edit(point.getId(), {title: t, description: d})
		).then(function(result) {

		});

		return false;
	}

};