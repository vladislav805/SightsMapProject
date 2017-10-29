var storage = (function(s) {
	return {
		get: function(name) { return s.getItem(name) },
		set: function(name, value) { return s.setItem(name, value) },
		has: function(name) { return s.contains(name) },
		remove: function(name) { return s.removeItem(name) }
	};
})(window.localStorage);

/*HTMLElement.prototype.addClass=function(a){Array.isArray(a)?a.forEach(function(a){this.classList.add(a)},this):this.classList.add(a);return this};HTMLElement.prototype.removeClass=function(a){Array.isArray(a)?a.forEach(function(a){this.classList.remove(a)},this):this.classList.remove(a);return this};HTMLElement.prototype.hasClass=function(a){return this.classList.contains(a)};HTMLElement.prototype.css=function(a,c){if(a&&c&&"string"===typeof a)this.style[a]=c;else for(var b in a)if(a.hasOwnProperty(b)){this.style[b]=a[b]}return this};Number.prototype.range=function(i,x){return Math.min(Math.max(this,i),x)};String.prototype.replaceHTML=function(){return this.replace(/"/img,"&quot;").replace(/</img,"&lt;").replace(/>/img,"&gt;")};String.prototype.replacePlain=function(){return this.replace(/\n/img,"<br/>")};Math.rad=function(d){return d*Math.PI/180;};*/

function copy2clipboard(b){var a=document.createElement("textarea");a.style.position="fixed";a.style.top="0";a.style.left="0";a.style.width="2em";a.style.height="2em";a.style.padding=0;a.style.border="none";a.style.outline="none";a.style.boxShadow="none";a.style.background="transparent";a.value=b;document.body.appendChild(a);a.select();var c;try{c=document.execCommand("copy")}catch(d){c=!1}document.body.removeChild(a);return c}

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

	POINT_CREATE: "onPointCreate",
	POINT_CREATED: "onPointCreated",


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
	MARK_FILTER_UPDATED:"onMarkFilterUpdated",


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
		90: "Неизвестная ошибка"
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
		Main.mSession = null;
		API.session.setAuthKey(null);
		Main.fire(EventCode.SESSION_CLOSED);
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
	 * @param {*} args
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

		g("hatPhoto").src = user.getPhoto(0);
		g("hatName").textContent = user.getFirstName();
		g("hatLogin").textContent = "@" + user.getLogin();


		cl.remove(Const.CLASS_NAME_USER_UNAUTHORIZED);
		cl.add(Const.CLASS_NAME_USER_AUTHORIZED);
	},
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
 * Конвертация n в целое число
 * @param {number} n
 * @returns {int}
 */
function toInt(n) {
	return parseInt(n);
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
 * @param {HTMLSelectElement} select
 * @returns {*}
 */
function getSelectedValuesInSelect(select) {
	return Array.prototype.map.call(select.querySelectorAll("option:checked"), function(option) {
		return option.value;
	});
}

/**
 * Return object of values of form
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
 * Get value from form element
 * @param {HTMLElement|String} node
 * @returns {String|Number|null}
 */
function getValue(node) {
	if (typeof node === "string") {
		node = g(node);
	}
	switch (node.tagName.toLowerCase()) {

		case "input":
			/** @var {HTMLInputElement} node */
			switch (node.type) {

				case "text":
				case "password":
				case "hidden":
				case "email":
					return node.value;

				case "checkbox":
				case "radio":
					return node.checked ? node.value : null;

				default:
					return null;
			}
			break;

		case "select":
			/** @var {HTMLSelectElement} node */
			//noinspection JSUnresolvedVariable
			return node.querySelectorAll("option:checked").length > 1 ? getSelectedValuesInSelect(node) : node.options[node.selectedIndex].value;

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
 * @returns {object}
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
 * Returns serialized
 * @returns {{type: string, lat: Number, lng: Number, zoom: Number, pointId: Number, accessCode: string, userId: Number}}
 */
function getAddressParams() {
	var d = get();
	return {
		type: get(d.t),
		lat: parseFloat(d.lat),
		lng: parseFloat(d.lng),
		zoom: parseInt(d.z),
		pointId: parseInt(d.id)
	};
}