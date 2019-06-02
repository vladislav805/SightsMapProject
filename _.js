var ymaps = {
	Map: function() {},

	GeoObject: function() {},

	Clusterer: function() {},

	ObjectManager: function() {},

	Monitor: function() {},

	Placemark: function() {},

	GeoObjectCollection: function() {},

	ready: function() {},

	util: {},

	control: {
		Button: function() {},
		TypeSelector: function() {},
		RulerControl: function() {},
		ZoomControl: function() {},
		GeolocationControl: function() {},
		SearchControl: function() {},
		ListBox: function () {},
		ListBoxItem: function () {},
		isSelected: function() {},
		select: function() {},
		deselect: function () {}
	},

	geolocation: {
		get: function() {}
	},

	geoObjects: {
		add: function() {},
		remove: function() {}
	},

	getObjectState: function() {},

	cluster: {},

	events: {
		add: function(name, listener, context) { return name+listener + context; },
		remove: function() {},
		once: function() {}
	},

	objects: {},

	balloon: {
		open: function() {},
		close: function() {},
		isOpen: function() {},
	},

	geometry: {
		getCoordinates: function() {},
		setCoordinates: function() {},
		insert: function() {},
		pixel: {
			Rectangle: function() {}
		}
	},

	options: {
		set: function() {},
		setFilter: function() {},
	},

	/**
	 * @param {[float, float]} c
	 * @param {object} o
	 */
	geocode: function(c, o) {},

	templateLayoutFactory: {
		createClass: function() {},
		getElement: function() {}
	},

	template: {
		filtersStorage: {
			/**
			 *
			 * @param {string} s
			 * @param {function(ymaps, string, string)} f
			 */
			add: function(s, f) {}
		}
	},

	shape: {
		Rectangle: function() {}
	}
};

ymaps.Map.prototype.setCenter = function() {};
ymaps.Map.prototype.getCenter = function() {};
ymaps.Map.prototype.setType = function() {};
ymaps.Map.prototype.getType = function() {};
ymaps.Map.prototype.setZoom = function() {};
ymaps.Map.prototype.getZoom = function() {};
ymaps.Map.prototype.getBounds = function() {};
ymaps.Map.prototype.panTo = function() {};
ymaps.GeoObjectCollection.prototype.removeAll = function() {};
ymaps.balloon.prototype.properties = {};

var d = {
	z: 1
};


var API = {

	/**
	 * @type {{ownerId: int, sightId: int, markIds: int[], lat: float, lng: float, dateCreated: int, dateUpdated: int=, title: string, description: string, city: City|null, photo: Photo|null, isVerified: boolean, isArchived: boolean, visitState: int=, rating: int, canModify: boolean=, interest: {value: float}=}}
	 */
	Sight: {},

	/**
	 * @type {{cityId: int, name: string, parentId: int|null, name4child: string, description: string, radius: int}}
	 */
	City: {},

	/**
	 * @type {{cityId: int, name: string, parentId: int|null, lat: float, lng: float, count: int}}
	 */
	StandaloneCity: {},

	/**
	 * @type {{userId: int, login: string, firstName: string, lastName: string, sex: string, lastSeen: int, isOnline: boolean, photo: Photo|null, city: City|null}}
	 */
	User: {},

	/**
	 * @type {{ownerId: int, photoId: int, date: int, photo200: string, photoMax: string, type: int, latitude: float, longitude: float, prevailColors: int[]}}
	 */
	Photo: {}
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
	},
	String: {
		extend: function() {}
	},
	Number: {
		hex: function() {}
	},
	Array: {
		isEqual: function() {}
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
String.prototype.toNumber = function(n) {};
Date.prototype.format = function(s) {};
Date.prototype.relative = function(s) {};

//noinspection JSUnusedGlobalSymbols
var PlacemarkIcon = {

	mCache: {},

	mSchema: '<svg width="34" height="42" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="-4.452%" y1="87.587%" x2="92.777%" y2="7.839%" id="a" stop-color="#231F20"><stop offset="0%"/><stop stop-opacity="0" offset="100%"/></linearGradient></defs><g fill="none"><path d="M12.718 39.666c4.71-2.394 17.82-11.516 18.305-11.978.55-.52 1.06-1.06 1.49-1.62 3-4 1.122-7.62-4.43-8.03l-1.678-.16c-.96 3.428-3.223 7.603-6.433 12.33-1.47 2.167-3.043 4.3-4.615 6.31-.55.704-1.062 1.343-1.522 1.905l-.548.663c-.118.154-.27.322-.465.495l-.103.088zm0 0" opacity=".5" fill="url(#a)"/><path d="M0 13.5C0 6.044 6.044 0 13.5 0S27 6.044 27 13.5c0 .782-.067 1.557-.198 2.317l.012.166-.037.228c-.595 3.703-3.054 8.474-6.805 13.997-1.47 2.166-3.043 4.298-4.615 6.31-.55.703-1.062 1.342-1.522 1.904l-.548.664c-.118.154-.27.322-.465.495-.224.195-.464.36-.727.482-.406.186-.865.263-1.402.17l-.216-.048c-.862-.226-1.463-.88-1.66-1.68-.107-.434-.087-.814-.006-1.18l.05-.194 2.77-10.258C5.06 25.962 0 20.322 0 13.5zm0 0" fill-opacity=".8" fill="#fff"/><path d="M2 13.5C2 7.15 7.15 2 13.5 2S25 7.15 25 13.5c0 .767-.075 1.517-.22 2.243l.022.15C23.517 23.898 11.72 37.842 11.72 37.842s-.376.525-.734.41c-.368-.094-.205-.562-.205-.562l3.433-12.71c-.236.014-.474.022-.713.022C7.15 25 2 19.85 2 13.5zm0 0" fill="#%s"/><circle fill="#fff" cx="13.5" cy="13.5" r="8.5"/><circle fill="#%s" cx="13.5" cy="13.5" r="4.5"/></g></svg>',

	get: function (color) {
		return this.mCache[color] ? this.mCache[color] : this.create(color);
	},

	create: function (color) {
		return this.mCache[color] = URL.createObjectURL(new Blob([this.mSchema.replace(/%s/gi, ColorUtils.getHEX(color))], {type: "image/svg+xml"}));
	},
};

/**
 * @type {{errorId: Number, message: string, extra: object=}}
 */
var APIError = {};