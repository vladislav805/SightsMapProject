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

var storage = (function(s) {
	return {
		get: function(name) { return s.getItem(name) },
		set: function(name, value) { return s.setItem(name, value) },
		has: function(name) { return s["contains"](name) },
		remove: function(name) { return s.removeItem(name) }
	};
})(window.localStorage);