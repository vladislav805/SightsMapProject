/**
 * Возвращает значение параметра в адресе или все параметры как объект
 * @param {string=} name
 * @returns {object|string}
 */
function get(name) {
	var h = window.location.search.substring(1).split("&"), d = {}, n;
	h.forEach(function(i) {
		i = i.split("=");
		n = i.shift();
		d[n] = decodeURIComponent(i.join(""));
	});
	return name ? d[name] : d;
}

/**
 * Возвращает данные из адресной строки
 * @returns {{type: string, lat: number, lng: number, zoom: int}}
 */
function getAddressParams() {
	var d = get();
	return {
		type: String(get(d.t)),
		lat: parseFloat(d.lat),
		lng: parseFloat(d.lng),
		zoom: parseInt(String(d.z))
	};
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
 * Возвращает элемент из DOM по ID
 * @param {string} id
 * @returns {HTMLElement}
 */
function ge(id) {
	return document.getElementById(id);
}

function initSpoilers() {
	const spoilers = document.querySelectorAll(".spoiler:not(.spoiler--inited)");

	Array.from(spoilers).forEach(item => {
		const head = item.querySelector(".spoiler-head");

		if (!head || !content) {
			return;
		}

		head.addEventListener("click", e => item.classList.toggle("spoiler--open"))
	});
}

/**
 *
 * @param {HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement|RadioNodeList|Node} node
 * @returns {*}
 */
function getValue(node) {
	switch (node.tagName.toLowerCase()) {
		case "select":
			if (node.multiple) {
				return !1;
			}

			return node.options[node.selectedIndex].value;

		case "textarea":
			return node.value.trim();

		case "input":
		default:
			if (node instanceof RadioNodeList) {
				return node.value;
			}
			switch (node.type) {
				case "checkbox":
					if (!node.form[node.name]) {
						return node.checked;
					}

					if (!("length" in node.form[node.name])) {
						return node.checked ? node.value : null;
					}

					return Array.from(node.form[node.name]).map(node => node.checked ? node.value : false).filter(i => i !== false);

				case "submit":
					return null;

				default:
					return node.value.trim();
			}
	}
}

function getBody() {
	return document.getElementsByTagName("body")[0];
}

/**
 * @param {HTMLFormElement|Node|HTMLElement} form
 * @returns {object}
 */
function shakeOutForm(form) {
	var res = {};
	for (var i = 0, node; node = form.elements[i]; ++i) {
		if (node.name) {
			res[node.name] = getValue(node);
		}
	}
	return res;
}

function emptyNode(node) {
	let child;
	while (child = node.lastChild) {
		node.removeChild(child);
	}
}

function setOpacity(node, state) {
	node.classList[state ? "add" : "remove"]("element--opacity");
}

function getCookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}

function clone(obj) {
	var copy;

	// Handle the 3 simple types, and null or undefined
	if (null == obj || "object" != typeof obj) {
		return obj;
	}

	// Handle Date
	if (obj instanceof Date) {
		copy = new Date();
		copy.setTime(obj.getTime());
		return copy;
	}

	// Handle Array
	if (obj instanceof Array) {
		copy = [];
		for (var i = 0, len = obj.length; i < len; i++) {
			copy[i] = clone(obj[i]);
		}
		return copy;
	}

	// Handle Object
	if (obj instanceof Object) {
		copy = {};
		for (var attr in obj) {
			if (obj.hasOwnProperty(attr)) {
				copy[attr] = clone(obj[attr]);
			}
		}
		return copy;
	}

	throw new Error("Unable to copy obj! Its type isn't supported.");
}

var storage = (function(s) {
	var m = {
		get: function(name) {
			var meta, data = s.getItem(name);
			if (meta = findMeta(name)) {
				if (meta.json) {
					try {
						data = JSON.parse(data);
					} catch (e) {
						data = null;
					}
				}
			}
			return data;
		},

		set: function(name, value) {
			if (typeof value !== "string") {
				value = JSON.stringify(value);
				putMeta(name, {json: 1});
			}
			return s.setItem(name, value);
		},

		has: function(name) {
			return s.hasOwnProperty(name) && s[name] !== undefined && s[name] !== null;
		},

		remove: function(name) {
			if (findMeta(name)) {
				putMeta(name, null);
			}
			return s.removeItem(name)
		}
	};

	var KEY_META = "__meta";

	var getMeta = function() {
		var str = s.getItem(KEY_META);
		var data;
		try {
			data = str ? JSON.parse(str) : {};
		} catch (e) {
			data = {};
			console.error("Meta record was corrupted. Data may be lost.");
		}

		return data;
	};

	var setMeta = function(data) {
		s.setItem(KEY_META, JSON.stringify(data));
	};

	var putMeta = function(name, val) {
		var meta = getMeta();
		if (val) {
			meta[name] = val;
		} else {
			delete meta[name];
		}
		setMeta(meta);
	};

	var findMeta = function(name) {
		var meta = getMeta();
		return meta[name];
	};

	return m;
})(window.localStorage);

function updateHeadRibbonBackgroundOpacity() {
	const node = ge("head");
	const img = ge("ribbon-image");

	if (!node) {
		return;
	}

	let alpha;
	if (node.classList.contains("head--ribbon")) {
		const threshold = 250;
		alpha = Math.min((100 * (window.pageYOffset || document.body.scrollTop) / threshold) / 100, 1);
	} else {
		alpha = 1;
	}

	node.style.background = "rgba(0, 150, 136, " + alpha + ")";
	node.style.boxShadow = "0 0 4px rgba(0, 0, 0, " + (.4 * alpha) + ")";

	if (img) {
		img.style.transform = "translate3d(0, " + (alpha * 50) + "px, 0)";
	}
}

const CLASS_MENU__OPENED = "menu--open";
const CLASS_LOGO_LOGO = "head-logo--logo";

function init() {
	const logo = ge("head-logo");
	const body = getBody();

	logo.addEventListener("click", event => {
		if (body.classList.contains(CLASS_MENU__OPENED)) {
			event.preventDefault();
			body.classList.remove(CLASS_MENU__OPENED);
			logo.classList.add(CLASS_LOGO_LOGO);
			return;
		}

		if (body.clientWidth < 900) {
			event.preventDefault();
			body.classList.add(CLASS_MENU__OPENED);
			logo.classList.remove(CLASS_LOGO_LOGO);
		} else {
			navigateTo("/", null);
			event.preventDefault();
			return false;
		}
	});
}

function openLoginForm() {
	const make = () => {
		return new FullScreenTextSlider([
			{
				id: 0,
				title: "Sights Map userarea",
				backgroundColor: "#009688",
				textColor: "#ffffff",
				text: "<div class='slides-login'><button onclick='window.__loginFSTSlider.go(1);'>Вход по паре username/email + пароль</button><a class='button' href='/userarea/vk'>Вход через VK</a><a class='button' href='/userarea/create'>Регистрация</a></div>",
				nextId: -1
			},
			{
				id: 1,
				title: "Авторизация",
				backgroundColor: "#009688",
				textColor: "#ffffff",
				text: "<div id='slide-login-error'></div><p>Логин или e-mail</p><input type='text' class='slide-login-login' name='login'>"
			},
			{
				id: 2,
				title: "Авторизация",
				backgroundColor: "#448AFF",
				textColor: "#ffffff",
				text: "<p>Пароль</p><input type='password' class='slide-login-password' name='password'><span onclick='window.__loginFSTSlider.go(3);'>не помню пароль</span>",
				nextId: FullScreenTextSlider.SLIDE_ID_END
			},
			{
				id: 3,
				title: "Восстановление доступа",
				backgroundColor: "#77420f",
				textColor: "#ffffff",
				text: "<div id='slide-restore-error'></div><p>Пожалуйста, введите ниже свой логин или e-mail от аккаунта, к которому Вы не помните пароль. Мы вышлем на привязанную почту ссылку для сброса пароля</p><input type='text' class='slide-login-login' name='userdata'>",
				previousId: 0,
				nextId: FullScreenTextSlider.SLIDE_ID_END
			}
		], {
			previous: { label: "Назад" },
			next: { label: "Далее" },
			end: { label: "Готово" },
			animationDuration: 200,
			onAfterSlideChange: function(id) {
				switch (id) {
					case 1:
					case 2:
						setTimeout(() => window.__loginFSTSlider.querySelector("input", true).focus(), 510);
						break;

				}
			},
			onEnd: function(slides) {
				const opts = {
					login: slides.querySelector("[type=text]").value,
					password: slides.querySelector("[type=password]").value
				};

				API.account.getAuthKey(opts.login, opts.password).then(res => {
					setCookie("token", res.authKey, {
						expires: 60 * 60 * 24 * 30,
						path: "/"
					});
					window.location.reload();
				}).catch(err => {
					console.log(err);
					slides.querySelector("#slide-login-error").textContent = err.error.message;
					slides.go(1);
				});
			}
		});
	};

	const open = () => {
		if (!window.__loginFSTSlider) {
			window.__loginFSTSlider = make();
		}

		window.__loginFSTSlider.inject();
	};

	if (typeof window.FullScreenTextSlider !== "function") {
		insertModules(["/css/slides.css"], MODULE_CSS);
		insertModules(["/pages/js/ui/slides.js"], MODULE_JS, data => open());
	} else {
		open();
	}

	return false;
}

/**
 *
 * @param {function} callback
 */
function onReady(callback) {
	if (document.readyState === "complete") {
		callback();
	} else {
		window.addEventListener("DOMContentLoaded", callback.bind(null));
	}
}

function setCookie(name, value, options) {
	options = options || {};

	var expires = options.expires;

	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}

	value = encodeURIComponent(value);

	var updatedCookie = name + "=" + value;

	for (var propName in options) {
		if (!options.hasOwnProperty(propName)) {
			continue;
		}
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}

	document.cookie = updatedCookie;
}

/**
 * @param degrees
 * @returns {number}
 */
Math.radians = function(degrees) {
	return degrees * Math.PI / 180;
};


function getDistance(x, y) {
	const rad_x_lat = Math.radians(x.lat);

	return (
		6371000 * Math.acos(
			Math.cos(Math.radians(y.lat)) * Math.cos(rad_x_lat) * Math.cos(Math.radians(x.lng) - Math.radians(y.lng)) + Math.sin(Math.radians(y.lat)) * Math.sin(rad_x_lat)
		)
	);
}

/**
 *
 * @param {HTMLElement|HTMLFormElement} form
 * @param {function} listener
 */
function setFormListener(form, listener) {
	form.addEventListener("submit", listener.bind(form));
}

window.addEventListener("scroll", updateHeadRibbonBackgroundOpacity);
window.addEventListener("DOMContentLoaded", updateHeadRibbonBackgroundOpacity);
window.addEventListener("load", () => {
	Sugar.Date.extend();
	Sugar.Object.extend();
	Sugar.String.extend();
	Sugar.Number.extend();
	init();
});
