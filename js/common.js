var storage = (function(s) {
	return {
		get: function(name) { return s.getItem(name) },
		set: function(name, value) { return s.setItem(name, value) },
		has: function(name) { return s["contains"](name) },
		remove: function(name) { return s.removeItem(name) }
	};
})(window.localStorage);

/*HTMLElement.prototype.addClass=function(a){Array.isArray(a)?a.forEach(function(a){this.classList.add(a)},this):this.classList.add(a);return this};HTMLElement.prototype.removeClass=function(a){Array.isArray(a)?a.forEach(function(a){this.classList.remove(a)},this):this.classList.remove(a);return this};HTMLElement.prototype.hasClass=function(a){return this.classList.contains(a)};HTMLElement.prototype.css=function(a,c){if(a&&c&&"string"===typeof a)this.style[a]=c;else for(var b in a)if(a.hasOwnProperty(b)){this.style[b]=a[b]}return this};String.prototype.replaceHTML=function(){return this.replace(/"/img,"&quot;").replace(/</img,"&lt;").replace(/>/img,"&gt;")};String.prototype.replacePlain=function(){return this.replace(/\n/img,"<br/>")};Math.rad=function(d){return d*Math.PI/180;};*/

Number.prototype.range=function(i,x){return Math.min(Math.max(this,i),x)};

function copy2clipboard(b){var a=document.createElement("textarea");a.style.position="fixed";a.style.top="0";a.style.left="0";a.style.width="2em";a.style.height="2em";a.style.padding="0";a.style.border="none";a.style.outline="none";a.style.boxShadow="none";a.style.background="transparent";a.value=b;document.body.appendChild(a);a.select();var c;try{c=document.execCommand("copy")}catch(d){c=!1}document.body.removeChild(a);return c}

var Const = {
	AUTH_KEY: "authKey",
	USER_ID: "userId",
	POINT_ID: "pointId",

	LAST_LAT: "lastLat",
	LAST_LNG: "lastLng",
	LAST_ZOOM: "lastZoom",

	POINT_MAX_COUNT: 350,

	CLASS_NAME_USER_AUTHORIZED: "user-authorized",
	CLASS_NAME_USER_UNAUTHORIZED: "user-unauthorized",

	CLASS_NAME_BUTTON_DISABLED: "button-disabled",

	DEFAULT_FULL_DATE_FORMAT: "%d/%m/%Y %H:%M"
};

var EventCode = {
	SESSION_COMMITTED: "onSessionCommitted",
	SESSION_CLOSED: "onSessionClosed",

	MAP_DONE: "onMapDone",
	MAP_BOUNDS_CHANGED: "onMapBoundsChanged",
	MAP_FILTER_CHANGED: "onMapFilterChanged",
	MAP_PLACEMARK_REMOVE: "onPlacemarkRemove",
	MAP_FILTER_UPDATED:"onMapFilterUpdated",

	POINT_CREATE: "onPointCreate",
	POINT_CREATED: "onPointCreated",
	POINT_HIGHLIGHT: "onPointHighlight",
	POINT_SHOW: "onPointShow",
	POINT_LIST_UPDATED: "onPointListUpdated",
	POINT_CLICK: "onPointClick",
	POINT_EDIT: "onPointEdit",
	POINT_EDITED: "onPointEdited",
	POINT_MOVE: "onPointMove",
	POINT_MOVED: "onPointMoved",
	POINT_REMOVED: "onPointRemoved",

	MARK_LIST_UPDATED: "onMarkListLoaded",
	MARK_CREATED: "onMarkCreated",
	MARK_EDITED: "onMarkEdited",
	MARK_REMOVED: "onMarkRemoved",

	COMMENT_LIST_LOADED: "onCommentListLoaded",
	COMMENT_ADDED: "onCommentAdded",
	COMMENT_REMOVED: "onCommentRemoved",

	EVENT_CENTER_UPDATED: "onEventCenterUpdated",
	EVENT_CENTER_COUNT_UNVIEWED_UPDATED: "onEventCenterCountUnviewedUpdated",
	EVENT_CENTER_SEND_VIEWED: "onEventCenterSendViewed",
	EVENT_CENTER_RESET_VIEWED: "onEventCenterResetViewed"
};

var Main = {

	errors: {
		50: "Неверная пара логин/пароль",
		51: "Такой логин уже занят",
		52: "Пароль не может быть длиной менее 6 символов",
		53: "Разве имя/фамилия могут быть такими короткими?",
		54: "Сессия не найдена. Пожалуйста, переавторизуйтесь",
		55: "Доступ запрещен",
		57: "Метка не найдена",
		59: "Неверные координаты",
		62: "Категория не найдена",
		70: "Фотография не найдена",
		80: "Комментарий не найден",
		90: "Неизвестная ошибка",
		91: "Слишком частое действие"
	},

	mListeners: {},

	mSession: null,

	/**
	 * Замена сессии
	 * @param {Session} session
	 * @returns {Main}
	 */
	setSession: function(session) {
		Main.mSession = session;
		API.session.setAuthKey(session.getAuthKey());
		Main.fire(EventCode.SESSION_COMMITTED, {session: session});
		return this;
	},

	/**
	 * Закрытие сессии
	 */
	closeSession: function() {
		API.account.logout().then(function() {
			Main.mSession = null;
			API.session.setAuthKey(null);
			Main.fire(EventCode.SESSION_CLOSED, {});
		});
	},

	/**
	 * Возвращает текущую сессию пользователя
	 * @returns {Session}
	 */
	getSession: function() {
		return this.mSession;
	},

	/**
	 * Добавляет слушателя события
	 * @param {string} eventType
	 * @param {function} listener
	 * @returns {Main}
	 */
	addListener: function(eventType, listener) {
		var storage = Main.mListeners;
		if (storage[eventType]) {
			storage[eventType].push(listener)
		} else {
			storage[eventType] = [listener];
		}
		return this;
	},

	/**
	 * Триггерит событие
	 * @param {string} eventType
	 * @param {*=} args
	 */
	fire: function(eventType, args) {
		console.log("fire", eventType);
		var items = Main.mListeners[eventType];

		if (items) {
			items.forEach(function(listener) {
				listener.call(Main, args);
			});
		}
	},

	/**
	 * Удаляет обработчик события
	 * @param {string} eventType
	 * @param {function} listener
	 * @returns {Main}
	 */
	removeListener: function(eventType, listener) {
		var storage = Main.mListeners[eventType];

		if (!storage) {
			return this;
		}

		for (var i = 0, l = storage.length; i < l; ++i) {
			if (storage[i] === listener) {
				storage.splice(i, 1);
				break;
			}
		}

		return this;
	},

	/**
	 * Слушатель события: заменяет данные о пользователе по сессии
	 * @param {{session: Session}} args
	 */
	showCurrentUser: function(args) {
		var user = args.session.getUser(), cl = getBody().classList;

		if (args.session.getState() === Session.STATE_NONE) {
			cl.add(Const.CLASS_NAME_USER_UNAUTHORIZED);
			cl.remove(Const.CLASS_NAME_USER_AUTHORIZED);
			return;
		}

		g("hatPhoto")["src"] = user.getPhoto().get(Photo.size.THUMBNAIL);
		g("hatName").textContent = user.getFirstName();
		g("hatLogin").textContent = "@" + user.getLogin();


		cl.remove(Const.CLASS_NAME_USER_UNAUTHORIZED);
		cl.add(Const.CLASS_NAME_USER_AUTHORIZED);

		API.account.setStatus(true);
	},
};

var ALLOWED_TAGS = "a|b|br|strong|i|ins|s|u|ul|ol|li".split("|");

String.prototype.safetyHTML = function() {
	var s = this;

	s = s.replace(/\n/, "<br>").replace(/<\/?([a-z]+)[^>]*>/igm, function(all, tag, position) {
		return ~ALLOWED_TAGS.indexOf(tag) ? all : "";
	});
	s = s.replace(/<[^\s]+?[\s"'](on[^=]+=(["'])([\S\s]*?)\2+?)[^>]*>/img, function(all, eventAttributeFull) {
		return all.replace(eventAttributeFull, " ");
	});

	return s;
};

/**
 * Возвращает элемент из DOM
 * @param {string} id
 * @returns {HTMLElement}
 */
function g(id) {
	return document.getElementById(id)
}

/**
 * Возвращает фразу в зависимости от пола пользователя
 * @param {User} user
 * @param {string[]} arr
 * @return {string}
 */
function getWordBySex(user, arr) {
	return arr[(user.sex - 1).range(0, 1)];
}

/**
 * Возвращает элемент тела документа
 * @returns {*}
 */
function getBody() {
	return document.getElementsByTagName("body")[0];
}

/**
 * Возвращает массив значений выбранных элементов в селекте
 * @param {HTMLElement} select
 * @returns {*}
 */
function getSelectedValuesInSelect(select) {
	return Array.prototype.map.call(select.querySelectorAll("option:checked"), function(option) {
		return option.value;
	});
}

/**
 * Возвращает сериализованные данные формы
 * @param {HTMLFormElement} form
 * @returns {object}
 */
function getFormParams(form) {
	var obj = {};
	for (var i = 0, l, v; l = form.elements.item(i); ++i) {
		v = getValue(l);
		if (v !== null) {
			obj[l.name] = v;
		}
	}
	return obj;
}

/**
 * Возвращает значение из элемента формы
 * @param {HTMLElement|string} node
 * @returns {string|null}
 */
function getValue(node) {
	if (typeof node === "string") {
		node = g(node);
	}
	switch (node.tagName.toLowerCase()) {

		case "input":
			/** @var {HTMLInputElement} node */
			switch (node["type"]) {

				case "text":
				case "password":
				case "hidden":
				case "email":
					return node["value"];

				case "checkbox":
				case "radio":
					return node["checked"] ? node["value"] : null;
			}
			return null;

		case "select":
			/** @var {HTMLSelectElement} node */
			//noinspection JSUnresolvedVariable
			return node.querySelectorAll("option:checked").length > 1 ? getSelectedValuesInSelect(node) : node.options[node.selectedIndex].value;

		case "textarea":
			return node["value"];

	}
	return null;
}

/**
 * Создание DOM-элемента
 * @param {string} tag
 * @param {object=} attr
 * @param {Node[]|HTMLElement[]=} child
 * @param {string=} html
 * @returns {Node|HTMLElement}
 */
function ce(tag, attr, child, html) {
	var node = document.createElement(tag);
	if (attr) {
		for (var key in attr) {
			if (attr.hasOwnProperty(key) && !key.indexOf("on")) {
				node.addEventListener(key.substring(2), attr[key]);
			} else {
				node.setAttribute(key, attr[key]);
			}
		}
	}
	if (child) {
		Array.prototype.forEach.call(child, function(i) {
			if (!i) {
				return;
			}

			if (typeof i === "string") {
				i = document.createTextNode(i);
			}

			node.appendChild(i);
		});
	}
	if (html) {
		node.innerHTML = html;
	}
	return node;
}

/**
 * Возвращает значение параметра в адресе или все параметры как объект
 * @param {string=} name
 * @returns {object|string}
 */
function get(name) {
	var h = window.location.search.substring(1).split("&"), d = {}, n;
	h.forEach(function (i) {
		i = i.split("=");
		n = i.shift();
		d[n] = decodeURIComponent(i.join(""));
	});
	return name ? d[name] : d;
}

/**
 * Возвращает данные из адресной строки
 * @returns {{type: string, lat: number, lng: number, zoom: int, pointId: int}}
 */
function getAddressParams() {
	var d = get();
	return {
		type: String(get(d.t)),
		lat: parseFloat(d.lat),
		lng: parseFloat(d.lng),
		zoom: parseInt(String(d.z)),
		pointId: parseInt(d.id)
	};
}

/**
 * Добавляет обработчик события на DOM-элемент
 * @param {string|string[]} events
 * @param {HTMLElement|Node} node
 * @param {function} listener
 */
function addEvent(events, node, listener) {
	if (typeof events === "string") {
		events = events.split(" ");
	}

	events.forEach(function(event) {
		node.addEventListener(event, listener);
	});
}

/**
 * Удаляет обработчик события с DOM-элемента
 * @param {string|string[]} events
 * @param {HTMLElement|Node} node
 * @param {function} listener
 */
function removeEvent(events, node, listener) {
	if (typeof events === "string") {
		events = events.split(" ");
	}

	events.forEach(function(event) {
		node.removeEventListener(event, listener);
	});
}

/**
 * Работа с цветом
 */
var ColorUtils = {

	/**
	 * Конвертация цвета, как целого числа, в HEX-строку
	 * @param {int|string} color
	 * @returns {string}
	 */
	getHEX: function(color) {
		return parseInt(color).hex(6);
	},

	light: {
		BRIGHT: 1,
		DARK: 0,
	},

	/**
	 * Определение цвета (светлый или темный)
	 * Возрвращает одно из значений из enum ColorUtils.light
	 * @param {string} hex
	 * @returns {int}
	 */
	getType: function(hex) {
		var r, g, b, x;
		hex = hex.replace("#", "");

		r = hex.substr(0, 2).toNumber(16);
		g = hex.substr(2, 2).toNumber(16);
		b = hex.substr(4, 2).toNumber(16);

		x = ((r * 299) + (g * 587) + (b * 114)) / 1000;

		return x >= 128 ? ColorUtils.light.BRIGHT : ColorUtils.light.DARK;
	}

};

function xConfirm(title, text, labelOk, labelCancel, onOk, onReject) {
	var content,
		footer,
		modal = new Modal({
			title: title,
			content: content = ce("form", null, [
				ce("div", {"class": "x-form-row"}, text)
			])
		});

	footer = getSubmitAndCancelButtons(labelOk, labelCancel, modal);

	addEvent("submit", content, function(event) {
		event.preventDefault();
		modal.release();
		onOk && onOk();
		return false;
	});
	addEvent("click", footer.firstElementChild, function() {
		onReject && onReject();
	});

	content.appendChild(footer);

	modal.show();
}

/**
 *
 * @param {int=} pointId
 * @returns {boolean|int}
 */
function isCurrentAsideOpenPointInfo(pointId) {
	var last = Aside.getLast() && Aside.getLast().getData() && Aside.getLast().getData().pointId;

	return pointId ? last === pointId : last;
}

var lang_error_str = {
	"0x42": "Изображение слишком мало. Ширина и высота должны быть более 720px."
};

/**
 *
 * @param {APIError} error
 * @returns {string}
 */
function getErrorStringByCode(error) {
	var hexId = "0x" + error.errorId.toString(16);
	return (hexId in lang_error_str ? lang_error_str[hexId] : "error #" + hexId) + ("message" in error ? " (reason: " + error.message  + ")" : "");
}