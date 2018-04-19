var EventCenter = {

	/**
	 * @var {HTMLElement}
	 */
	mHeadIcon: null,

	/**
	 * @var {int|null}
	 */
	mMainTimer: null,

	/**
	 * Интервал обновления
	 */
	INTERVAL: 60 * 1000,

	/**
	 * Инициализация центра событий
	 * Подхат элементов UI и старт таймера
	 */
	init: function() {
		this.mHeadIcon = g("head-events");

		addEvent("click", this.mHeadIcon, this.showFeed.bind(this));
	},

	/**
	 * Незамедлительная проверка событий и постановка на таймер
	 */
	start: function() {
		this.intervalUpdate();
		this.mMainTimer = setInterval(this.intervalUpdate.bind(this), EventCenter.INTERVAL);
	},

	/**
	 * Остановка следующего таймера
	 */
	stop: function() {
		clearTimeout(this.mMainTimer);
	},

	/**
	 * Метод для вызова запроса интервального обновления
	 */
	intervalUpdate: function() {
		this.request().then(function(result) {
			Main.fire(EventCode.EVENT_CENTER_UPDATED, result);
		});
	},

	/**
	 * Запрос на получение событий
	 * @returns {Promise.<{count: int, items: InternalEvent[], users: User[], points: Point[], photos: Photo[]}>}
	 */
	request: function() {
		return API.events.get().then(function(result) {
			result["items"] = result["items"].map(function(i) { return new InternalEvent(i); });
			result["users"] = result["users"].map(function(i) { return User.get(i); });
			result["photos"] = result["photos"].map(function(i) { return new Photo(i); });
			result["points"] = result["points"].map(function(i) {
				i.author = User.sCache.get(i.ownerId);
				i = Place.get(i);
				return i;
			});
			return result;
		});
	},

	/**
	 * Отпрвака запроса на сброс счетчика
	 * @returns {Promise.<boolean>}
	 */
	sendViewed: function() {
		return API.events.readAll().then(function(result) {
			Main.fire(EventCode.EVENT_CENTER_RESET_VIEWED, {});
			return result;
		});
	},

	/**
	 * Изменение количества новых событий в шапке у иконки
	 * @param {int} n
	 */
	setCount: function(n) {
		this.mHeadIcon.dataset.count = n;
	},

	/**
	 *
	 * @param {{
	 *     count: int,
	 *     items: InternalEvent[],
	 *     photos: Photo[],
	 *     users: User[],
	 *     points: Point[]
	 * }} data
	 */
	onUpdate: function(data) {
		Main.fire(EventCode.EVENT_CENTER_COUNT_UNVIEWED_UPDATED, data.items.reduce(function(prev, ev) {
			return prev + ev.isNew() * 1;
		}, 0))
	},

	/**
	 * Открытие модального окна для вывода событий, старт запроса на получение событий
	 */
	showFeed: function() {
		var content = ce("div", {"class": "feed-list"}, [getLoader()]),
			modal = new Modal({
				title: "Обновления",
				content: content
			});
		modal.setFooter(ce("input", {type: "button", value: "Закрыть", onclick: modal.release.bind(modal)}));
		modal.show();

		this.request().then(function(result) {
			Main.fire(EventCode.EVENT_CENTER_UPDATED, result);

			content.removeChild(content.firstChild);

			EventCenter.showItems(content, result, modal);
		});
	},

	/**
	 * Вывод всех полученных событий в модальное окно
	 * @param {HTMLElement} node
	 * @param {{
	 *     count: int,
	 *     items: InternalEvent[],
	 *     photos: Photo[],
	 *     users: User[],
	 *     points: Place[]
	 * }} data
	 * @param {Modal} modal
	 */
	showItems: function(node, data, modal) {
		var getExLink = function(label, action) {
				return ce("span", {"class": "a", onclick: function(event) {
					event.preventDefault();
					event.cancelBubble = true;
					event.stopPropagation();

					modal.release();

					action && action();

					return false;
				}}, label);
			},

			showPlaceOnMapBySubjectId = function(event) {
				var pl = Place.mCache.get(event.getSubjectId());
				if (pl) {
					Main.fire(EventCode.POINT_SHOW, {place: pl});
					Main.fire(EventCode.POINT_CLICK, {point: pl.getInfo()});
				} else {
					alert("Не могу найти место. Видимо, уже удалили.. :(");
				}
			},

			getEventInfo = function(event) {
				var user;
				switch (event.getType()) {

					case API.events.type.POINT_VERIFIED:
						return [
							getExLink("Место", showPlaceOnMapBySubjectId.bind(null, event)),
							" было верифицировано администратором"
						];

					case API.events.type.POINT_NEW_UNVERIFIED:
						return [
							"Было добавлено новое ",
							getExLink("место", showPlaceOnMapBySubjectId.bind(null, event))
						];

					case API.events.type.POINT_COMMENT_ADD:
						user = User.sCache.get(event.getActionUserId());
						return [
							getExLink(user.getFullName(), function() {
								Profile.requestUserInfo(event.getActionUserId());
							}),
							" ",
							getWordBySex(user, ["прокомментировала", "прокомментировал"]),
							" Ваше ",
							getExLink("место", showPlaceOnMapBySubjectId.bind(null, event))
						];

					case API.events.type.PHOTO_ADDED:
						user = User.sCache.get(event.getActionUserId());
						return [
							getExLink(user.getFullName(), function() {
								Profile.requestUserInfo(event.getActionUserId());
							}),
							" ",
							getWordBySex(user, ["добавила", "добавил"]),
							" фотографию к Вашему ",
							getExLink("месту", showPlaceOnMapBySubjectId.bind(null, event))
						];

					default:
						return [];
				}
			},


			getRow = function(event) {
				node.appendChild(ce("div", {"class": "feed-item" + (event.isNew() ? " feed-item-unread" : "")}, [
					ce("div", {"class": "feed-info"}, getEventInfo(event)),
					ce("div", {"class": "feed-date"}, null,
						(Date.now() - event.getDate()) / 1000 > 24 * 3600
							? event.getDate().format(Const.DEFAULT_FULL_DATE_FORMAT)
							: event.getDate().relative()
					)
				]));
			};


		data.items.forEach(getRow);

		Main.fire(EventCode.EVENT_CENTER_SEND_VIEWED, {});
	}

};