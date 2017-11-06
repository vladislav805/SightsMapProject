var ymaps = {
	Map: function() {},

	GeoObject: function() {},

	Clusterer: function() {},

	//Polyline: function() {},

	//Circle: function() {},

	GeoObjectCollection: function() {},

	ready: function() {},

	control: {
	//	Button: function() {},
		TypeSelector: function() {},
		RulerControl: function() {},
		ZoomControl: function() {},
		GeolocationControl: function() {},
		SearchControl: function() {}
	},

	geolocation: {
		get: function() {}
	},

	geoObjects: {
		add: function() {},
		remove: function() {}
	},

	getMap: function() {},
	getObjectState: function() {},

	cluster: {},

	events: {
		add: function(name, listener, context) { return name+listener + context; },
		remove: function() {},
		once: function() {}
	},

	balloon: {
		open: function() {},
		close: function() {},
		isOpen: function() {},
		//getData: function() {},
		getOverlay: function() {}
	},

	geometry: {
		//setCoordinates: function() {},
		getCoordinates: function() {},
		insert: function() {},
		//getLength: function() {},
		//setRadius: function() {}
	},

	options: {
		set: function() {}
	},

	margin: {
		//addArea: function() {}
	},

	/**
	 * @param {[float, float]} c
	 * @param {object} o
	 */
	geocode: function(c, o) {}
};

ymaps.Map.prototype.setCenter = function() {};
ymaps.Map.prototype.getCenter = function() {};
ymaps.Map.prototype.setType = function() {};
ymaps.Map.prototype.getType = function() {};
ymaps.Map.prototype.setZoom = function() {};
ymaps.Map.prototype.getZoom = function() {};
ymaps.Map.prototype.getBounds = function() {};
ymaps.GeoObjectCollection.prototype.removeAll = function() {};
ymaps.balloon.prototype.properties = {};
//yamas.balloon.overlay.getBalloonElement = function() {};

var d = {
	z: 1
};

var point = {
	author: null,
	coords: [],
	network: {}
};

var Sugar = {
	Date: { extend: function() {} },
	Object: {
		extend: function() {},
		toQueryString: function() {},
		defaults: function(a, b) {}
	}
};

var URL = {

	createObjectURL: function() {}
};

var baguetteBox = {
	/**
	 * @param {string} str
	 * @param {{noScrollbars: boolean=}} opts
	 */
	run: function(str, opts) {},
	destroy: function() {}
};

String.prototype.escapeHTML = function() {};
Date.prototype.format = function(s) {};