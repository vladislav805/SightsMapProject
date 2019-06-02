/**
 *
 * @param {HTMLElement|string} element
 * @param {{lat: float, lng: float, zoom: float}|null=} initialPosition
 * @param {{
 *     updateAddressOnChange: boolean=,
 *     onMapReady: function(ymaps.Map)=,
 *     onBoundsChanged: function({tl: {lat: float, lng: float}, br: {lat: float, lng: float}})=
 * }=} options
 * @constructor
 */
function BaseMap(element, initialPosition, options) {
	this.mOptions = options || {};

	this.mMap = new ymaps.Map(element instanceof HTMLElement ? element : ge(element), {
		center: initialPosition && initialPosition.lat && initialPosition.lng ? [initialPosition.lat, initialPosition.lng] : [0, 0],
		zoom: initialPosition && initialPosition.zoom || 4,
		controls: []
	}, {
		searchControlProvider: "yandex#map",
		suppressMapOpenBlock: true,
		yandexMapDisablePoiInteractivity: true
	});

	this.__initEvents();
	this.__initControls();
	this.__initCollections();
	!initialPosition && this.__setInitialStateMap(initialPosition);
	this.mOptions.onMapReady && this.mOptions.onMapReady.call(this, this.mMap);
}


BaseMap.prototype = {

	/** @var {ymaps.Map} */
	mMap: null,

	/** @var {{
	 *     updateAddressOnChange: boolean=,
	 *     onMapReady: function(ymaps.Map)=,
	 *     onBoundsChanged: function({tl: {lat: float, lng: float}, br: {lat: float, lng: float}})=
	 * }|null} */
	mOptions: null,

	/** @var {boolean} */
	mInitedPlace: false,

	/** @var {ymaps.Clusterer} */
	mPoints: null,

	/** @var {Bundle} */
	mCachePoint: null,

	/** @var {Bundle} */
	mCachePointGeoObject: null,

	/**
	 * Подвеска событий
	 */
	__initEvents: function() {
		this.mMap.events.add("boundschange", function() {
			this.setAddressByLocation();
			this.__savePosition();

			if (this.mOptions.onBoundsChanged) {
				var b = this.mMap.getBounds();

				this.mOptions.onBoundsChanged.call(this, {
					tl: {lat: b[0][0], lng: b[0][1]},
					br: {lat: b[1][0], lng: b[1][1]},
				});
			}
		}.bind(this));

		this.mMap.events.add("click", function(event) {
			if (this.mMap.balloon.isOpen()) {
				this.mMap.balloon.close();
			}
		}.bind(this));
	},

	__initControls: function() {

		var getPosition = function(top, left, size) {
			var obj = {
				float: "none",
				position: {
					top: BaseMap.CONTROLS_MARGIN * top + BaseMap.CONTROLS_SIZE * (top - 1),
					left: BaseMap.CONTROLS_MARGIN * left + BaseMap.CONTROLS_SIZE * (left - 1)
				}
			};

			if (size) {
				obj.size = size;
			}

			return obj;
		};

		/**
		 * Добавление контролов
		 */
		this.addControl(new ymaps.control.TypeSelector(["yandex#map", "yandex#hybrid"]), getPosition(1, 1, "small"));
		this.addControl(new ymaps.control.RulerControl({ options: { scaleLine: false } }), getPosition(1, 2, "small"));
		this.addControl("zoomControl", getPosition(2, 1));
		this.addControl(new ymaps.control.GeolocationControl(), getPosition(1, 3));
		this.addControl(new ymaps.control.SearchControl({
			options: {
				kind: "street",
				noSelect: true,
				noSuggestPanel: true,
				placeholderContent: "Поиск адреса",
				suppressYandexSearch: true
			}
		}), getPosition(1, 4, "auto"));
	},

	__initCollections: function() {
		this.mGeoObjectCollections = {};
	},

	addCollection: function(name, collection) {
		this.mMap.geoObjects.add(this.mGeoObjectCollections[name] = collection);
	},

	removeCollection: function(name) {
		this.mMap.geoObjects.remove(this.mGeoObjectCollections[name]);
	},

	restoreCollection: function(name) {
		if (this.mMap.geoObjects.indexOf(this.mGeoObjectCollections[name]) < 0) {
			this.mMap.geoObjects.add(this.mGeoObjectCollections[name]);
		}
	},

	getCollection: function(name) {
		return this.mGeoObjectCollections[name];
	},

	addControl: function() {
		this.mMap.controls.add.apply(this.mMap.controls, arguments);
	},

	__setInitialStateMap: function(g) {
		var coord;

		if (this.mOptions.updateAddressOnChange && !g && (g = get()) && g.c) {
			coord = g.c.split(BaseMap.COORD_GLUE);
			this.setLocationByCoordinates(parseFloat(coord[0]) || 0, parseFloat(coord[1]) || 0, parseFloat(g.z));
			return;
		}

		if (storage.get(BaseMap.LAST_LAT) && storage.get(BaseMap.LAST_LNG)) {
			this.setLocationByLastPosition();
		} else {
			this.setLocationByGeolocation();
		}
	},

	/**
	 * Установка положения карты по параметрам в адресе
	 */
	setLocationByCoordinates: function(lat, lng, z) {
		this.__setLocation(lat, lng, z || this.mMap.getZoom());
	},

	/**
	 * Обновление параметров в адресной строке по текущему положению карты
	 */
	setAddressByLocation: function() {
		if (!this.mOptions.updateAddressOnChange) {
			return;
		}
		var c = this.mMap.getCenter();
		var url = {
			c: [c[0].toFixed(6), c[1].toFixed(6)].join(BaseMap.COORD_GLUE),
			z: this.mMap.getZoom()
		};

		history.replaceState(null, "", "?" + Sugar.Object.toQueryString(url));
	},

	/**
	 * Установка положения карты по последним данным
	 */
	setLocationByLastPosition: function() {
		this.__setLocation(storage.get(BaseMap.LAST_LAT), storage.get(BaseMap.LAST_LNG), storage.get(BaseMap.LAST_ZOOM));
	},

	/**
	 * Сохранение в локальное хранилище браузера текущего положения карты
	 */
	__savePosition: function() {
		var m = this.mMap;
		var c = m.getCenter();

		storage.set(BaseMap.LAST_LAT, c[0]);
		storage.set(BaseMap.LAST_LNG, c[1]);
		storage.set(BaseMap.LAST_ZOOM, m.getZoom());
		console.log(c);
	},

	setLocationByGeolocation: function() {
		ymaps.geolocation.get({
			provider: "yandex",
			mapStateAutoApply: true
		}).then(function(result) {
			var c = result.geoObjects.position;
			console.log(c);
			this.__setLocation(c[0], c[1], 10);
		}.bind(this));
	},

	__setLocation: function(lat, lng, z) {
		this.mInitedPlace = true;
		this.mMap.setCenter([lat, lng], z);
	},

	getMap: function() {
		return this.mMap;
	}
};

BaseMap.COORD_GLUE = "_";
BaseMap.LAST_LAT = "lastLat";
BaseMap.LAST_LNG = "lastLng";
BaseMap.LAST_ZOOM = "lastZoom";
BaseMap.DEFAULT_FULL_DATE_FORMAT = "%d/%m/%Y %H:%M";
BaseMap.CONTROLS_MARGIN = 10;
BaseMap.CONTROLS_SIZE = 28;

/**
 *
 * @param {number} lat
 * @param {number} lng
 * @param {number} z
 * @param {{onReady: function}} options
 */
function showModalMap(lat, lng, z, options) {
	options = options || {};

	// Контейнер для карты
	let map = ce("div");

	// Модальное окно
	const modal = new Modal({
		title: options.title || "Карта",
		closeByClickOutside: false,
		content: map,
	});

	// Иницаилизация карты в контейнере
	const yMap = new ymaps.Map(map, {
		center: [lat, lng],
		zoom: z,
		controls: []
	}, {
		searchControlProvider: "yandex#map",
		suppressMapOpenBlock: true,
		yandexMapDisablePoiInteractivity: true
	});

	// Показываем окно
	modal.show();

	const onResize = event => {
		updateSizes();
	};

	window.addEventListener("resize", onResize);

	// Обработчик закрытия
	const onClose = () => {
		modal.release();
		window.removeEventListener("resize", onResize);
	};

	const modalContent = map.parentNode.parentNode;

	//
	// const computedStyle4ModalWrap = getComputedStyle(modalContent);

	// Изменение размеров
	const updateSizes = () => {

		console.log('update sizes')
		const clientWidth = document.documentElement.clientWidth;
		const clientHeight = document.documentElement.clientHeight;

		let width = clientWidth - 50; // maxSizeByCSS.width


		modalContent.style.width = width + "px";
		map.style.height = (clientHeight * .55) + "px";

		yMap.container.fitToViewport();
	};

	// Добавление кнопки закрытия
	modal.setFooter(ce("input", {type: "button", value: "Закрыть", onclick: onClose}));

	// Принудительное первичное обновление размеров модального окна и карты
	updateSizes();

	// Оповещение вызывателя о том, что карта готова
	options.onReady && options.onReady(yMap);
}