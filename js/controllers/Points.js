var Points = {

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
		var list = new AsidePage({
			pageTitle: "Список",
			pageContent: ce("div", {id: "asideListPoint"}, getLoader())
		});

		this.mPointsList = list.getContentNode();

		Aside.push(list);
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
	 * @returns {AsidePage}
	 */
	getInfoWidget: function(p) {
		var title = ce("h1", null, null, p.getTitle().escapeHTML()),
			description = ce("p", null, null, p.getDescription().safetyHTML()),

			dateCreated = ce("p", {"class": "info-date"}, null, "Создано " + p.dateCreated.format(Const.DEFAULT_FULL_DATE_FORMAT)),
			dateUpdated = p.dateUpdated ? ce("p", {"class": "info-date"}, null, "Отредактировано " + p.dateUpdated.format(Const.DEFAULT_FULL_DATE_FORMAT)) : null,

			marks = ce("div", {"class": "info-marks-items"}, Points.getMarksViewWidget(p)),

			author = ce("p", {"class": "info-author"}, [
				ce("div", null, null, "Автор: "),
				ce("a", {href: "user/" + p.mAuthor.getLogin()}, null, "@" + p.mAuthor.getLogin() + " (" + p.mAuthor.getFullName() + ")")
			]),

			actions = ce("div", {"class": "info-actions"}, this.getActions(p)),

			classes = ["info-point-wrap"];

		return new AsidePage({
			pageTitle: p.getTitle().escapeHTML(),
			pageContent: ce("div", {"class": classes.join(" ")}, [
				title,
				p.isVerified ? ce("div", {"class": "info-verified-row"}, [
					getIcon("e52d"),
					"Подтвержденное место"
				]) : null,
				description,
				marks,
				dateCreated,
				dateUpdated,
				author,
				actions,
				Photos.getWidget(p),
				Comments.getWidget(p)
			]),
			data: {
				pointId: p.getId()
			}
		});
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
			if (!p.isVerified && Main.getSession().getId() < 100) {
				var ver;
				items.push(ver = item("e8e8", "verify", "Верифицировать"));
				ver.addEventListener("click", Points.setVerify.bind(this, p, ver));
			}
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
			var mark = Marks.getBundle().get(i), hex = ColorUtils.getHEX(mark.getColor());
			return ce("div", {
				"class": "mark-item",
				"data-mark-id": i,
				style: "background: #" + hex + "; color: #" + (ColorUtils.getType(hex) === ColorUtils.light.DARK ? "FFF" : "000")
			}, mark.getTitle());
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
					this.getMarksSelect(point.getMarkIds() || []),
					ce("input", {type: "submit", value: "Сохранить"})
				])
			});
		modal.show();

		form.addEventListener("submit", Points.onSubmitEditOrCreate.bind(form, point, modal));
	},

	/**
	 *
	 * @param {int[]} selected
	 * @returns {HTMLElement}
	 */
	getMarksSelect: function(selected) {
		var wrap = ce("div", {"class": "category-editor-wrap"});

		/**
		 *
		 * @param {Mark} mark
		 * @param {boolean} isSelected
		 * @returns {Node|HTMLElement}
		 */
		var item = function(mark, isSelected) {
				var f, node = ce("label", {"class": "category-editor-item"}, [
					f = ce("input", {type: "checkbox", name: "markId", value: mark.getId()}),
					ce("span", null, null, mark.getTitle())
				]);
				f.checked = isSelected;
				return node;
			};


		Marks.getItems().forEach(function(mark) {
			wrap.appendChild(item(mark, !!~selected.indexOf(mark.getId())));
		});

		return wrap;
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
				toast.setButtons([]).setText("Удаление...");
				API.points.remove(point.getId()).then(function(result) {
					result && Main.fire(Const.POINT_REMOVED, {point: point, toast: toast});
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

		var t = getValue(this.title),
			d = getValue(this.description),
			m = [],
			done = function(d) {
				Main.fire(point.getId() ? EventCode.POINT_EDITED : EventCode.POINT_CREATED, point.getId() ? point : new Place(d));
			};

		m = Array.prototype.reduce.call(this.markId, function(markIds, current, index) {
			current.checked && markIds.push(current.value);
			return markIds;
		}, m).join(",");



		(!point.getId()
			? API.points.add({title: t, description: d, lat: point.getLat(), lng: point.getLng()})
			: API.points.edit(point.getId(), {title: t, description: d})
		).then(function(result) {
			modal.release();
			point.title = result.title;
			point.description = result.description;


			if (m !== point.getMarkIds()) {
				API.points.setMarks(point.getId(), m).then(function(res) {
					point.markIds = m.split(",").map(toInt);
					done(result);
				});
			} else {
				done(result);
			}

		});

		return false;
	},

	/**
	 * Отметка метки верифицированной
	 * @param {Point} point
	 * @param {HTMLElement} node
	 */
	setVerify: function(point, node) {
		API.request("points.setVerify", {pointId: point.getId(), state: 1}).then(function() {
			node.parentNode.removeChild(node);
			point.isVerified = true;
			new Toast("Верифицировано!").open(4000);
		});
	}

};