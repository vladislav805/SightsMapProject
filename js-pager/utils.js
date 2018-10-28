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
 * Возвращает элемент из DOM по ID
 * @param {string} id
 * @returns {HTMLElement}
 */
function ge(id) {
	return document.getElementById(id);
}

function getCookie(name) {
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}

var storage = (function(s) {
	return {
		get: function(name) { return s.getItem(name) },
		set: function(name, value) { return s.setItem(name, value) },
		has: function(name) { return s["contains"](name) },
		remove: function(name) { return s.removeItem(name) }
	};
})(window.localStorage);

function updateHeadRibbonBackgroundOpacity() {
	var node;
	if (!(node = document.querySelector(".head--ribbon"))) {
		return;
	}

	var threshold = 250;
	var alpha = Math.min((100 * window.pageYOffset / threshold) / 100, 1);

	node.style.background = "rgba(0, 150, 136, " + alpha+ ")";
	node.style.boxShadow = "0 0 4px rgba(0, 0, 0, " + (.4 * alpha) + ")";
}

function initializeStaticYandexMapsSizeImage() {
	var links = document.querySelectorAll(".sight-mapThumbnail-link");

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

function initApiClient() {
	if (API) {
		API.session.setAuthKey(getCookie("token"));
	}
}

window.addEventListener("scroll", updateHeadRibbonBackgroundOpacity);
window.addEventListener("DOMContentLoaded", updateHeadRibbonBackgroundOpacity);
window.addEventListener("resize", initializeStaticYandexMapsSizeImage);
window.addEventListener("DOMContentLoaded", initializeStaticYandexMapsSizeImage);
window.addEventListener("load", initApiClient);