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
		img.style.marginTop = (alpha * 50) + "px";
	}
}

function initializeStaticYandexMapsSizeImage() {
	var links = document.querySelectorAll(".sight-mapThumbnail-link");

	if (!links.length) {
		unbindYandexMapStaticImageListener();
		return;
	}

	var docSize = document.documentElement.clientWidth;
	var imgSize = 280;
	var mapScale = 15;

	if (docSize < 600) {
		imgSize = 150;
		mapScale = 14;
	}

	Array.prototype.forEach.call(links, function(link) {
		var img, ds;

		if (!link.firstChild) {
			img = document.createElement("img");
			link.appendChild(img);
		} else {
			img = link.firstChild;
		}

		ds = link.dataset;
		img.src = "https://static-maps.yandex.ru/1.x/?pt=" + ds.lng + "," + ds.lat + ",comma&z=" + mapScale + "&l=map&size=" + imgSize + "," + imgSize + "&lang=ru_RU&scale=1";
	});
}

function bindYandexMapStaticImageListener() {
	initializeStaticYandexMapsSizeImage();
	window.addEventListener("resize", initializeStaticYandexMapsSizeImage);
}

function unbindYandexMapStaticImageListener() {
	window.removeEventListener("resize", initializeStaticYandexMapsSizeImage);
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
});
