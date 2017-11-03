var Map = {

	/**
	 * @var {ymaps.Map}
	 */
	mMap: null,

	/**
	 * @var {ymaps.GeoObjectCollection}
	 */
	mPoints: null,

	/**
	 * @var {Bundle}
	 */
	mCachePoint: null,

	/**
	 * @var {Bundle}
	 */
	mCachePointGeoObject: null,

	/**
	 * Инициализация карты
	 */
	init: function() {
		this.mCachePoint = new Bundle;
		this.mCachePointGeoObject = new Bundle;

		window.ymaps && ymaps.ready(this.initMap.bind(this));
	},

	/**
	 * Создание карты и подвеска событий к ней
	 */
	initMap: function() {
		var d = getAddressParams();

		this.mMap = new ymaps.Map("map", {
			center: d.lat || d.lng ? [d.lat, d.lng] : [0, 0],
			zoom: d.zoom || 2,
			controls: []
		}, {
			searchControlProvider: "yandex#search",
			suppressMapOpenBlock: true
		});

		this.mMap.geoObjects.add(this.mPoints = new ymaps.GeoObjectCollection());

		/**
		 * Подвеска событий
		 */
		this.mMap.events.add("boundschange", Main.fire.bind(Main, EventCode.MAP_BOUNDS_CHANGED));
		this.mMap.events.add("click", function(event) {

			if (this.mMap.balloon.isOpen()) {
				this.mMap.balloon.close();
				return;
			}

			if (!Info.isOpened()) {
				var c = event.get("coords");
				Main.fire(EventCode.POINT_CREATE, {
					lat: c[0],
					lng: c[1]
				});
			} else {
				Info.close();
			}
		}.bind(this));


		// normal margin; button size (width/height)
		var nm = 10, bs = 28;

		/**
		 * Добавление контролов
		 */
		this.mMap.controls.add(new ymaps.control.TypeSelector(["yandex#map", "yandex#hybrid"]), {
			float: "none",
			position: {
				top: nm,
				left: nm
			},
			size: "small"
		});
		this.mMap.controls.add(new ymaps.control.RulerControl({options:{scaleLine: false}}), {
			float: "none",
			position: {
				top: nm,
				left: nm + bs + nm
			},
			size: "small"
		});
		this.mMap.controls.add(new ymaps.control.ZoomControl(), {
			float: "none",
			position: {
				top: nm + bs + nm,
				left: nm
			}
		});
		this.mMap.controls.add(new ymaps.control.GeolocationControl(), {
			float: "none",
			position: {
				top: nm,
				left: nm + bs + nm + bs + nm
			},
			size: "small"
		});
		this.mMap.controls.add(new ymaps.control.SearchControl({
			options: {
				kind: "street",
				noSelect: true,
				noSuggestPanel: true,
				placeholderContent: "Поиск",
				suppressYandexSearch: true
			}
		}), {
			float: "none",
			position: {
				top: nm,
				left: nm + bs + nm + bs + nm + bs + nm
			},
			size: "auto"
		});

		this.requestAutoLocation();

		/**
		 * Оповещаем о том, что карта готова
		 */
		Main.fire(EventCode.MAP_DONE, {});
	},

	/**
	 * Установка положения карты по параметрам в адресе
	 */
	setLocationByAddress: function() {
		var d = get();
		d.t && Map.mMap.setType("yandex#" + d.t);
		d.lat && d.lng && Map.mMap.setCenter([parseFloat(d.lat), parseFloat(d.lng)], Map.mMap.getZoom(), { useMapMargin: true });
		d.z && Map.mMap.setZoom(parseFloat(d.z));
	},

	/**
	 * Обновление параметров в адресной строке по текущему положению карты
	 */
	setAddressByLocation: function() {
		var c = this.mMap.getCenter(),
			pointId = Info.getArgs("pointId"),
			url = {lat: c[0].toFixed(6), lng: c[1].toFixed(6), t: Map.mMap.getType().split("#")[1], z: Map.mMap.getZoom()};

		pointId && (url.id = pointId);

		history.replaceState(null, "", "?" + Sugar.Object.toQueryString(url));
	},

	/**
	 * Установка положения карты по последним данным
	 */
	setLocationByLastPosition: function() {
		Map.mMap.setCenter([storage.get(Const.LAST_LAT), storage.get(Const.LAST_LNG)], storage.get(Const.LAST_ZOOM), { useMapMargin: true });
	},

	/**
	 * Сохранение в локальное хранилище браузера текущего положения карты
	 */
	savePosition: function() {
		var m = this.mMap, c = m.getCenter();

		storage.set(Const.LAST_LAT, c[0]);
		storage.set(Const.LAST_LNG, c[1]);
		storage.set(Const.LAST_ZOOM, m.getZoom());
	},

	setLocationByGeolocation: function() {
		ymaps.geolocation.get({
			provider: "yandex",
			mapStateAutoApply: true
		}).then(function(result) {
			console.log(result);
			Map.mMap.setCenter(result.geoObjects.position, 10);
		});
	},

	/**
	 * After loaded map, set place which will be displayed
	 */
	requestAutoLocation: function() {
		if (get("lat") && get("lng")) {
			Map.setLocationByAddress();
		} else if (storage.get(Const.LAST_LAT) && storage.get(Const.LAST_LNG)) {
			Map.setLocationByLastPosition();
		} else {
			Map.setLocationByGeolocation();
		}
	},

	/**
	 * Запрос данных об указанном участке карты. Вызывается после того, как карта была сдвинута пользователем или
	 * программно.
	 * После получения и обработки данных вызывается событие POINT_LIST_UPDATED с объектом {count: int, items: Place[]}
	 */
	requestPointsByBounds: function() {
		var b = Map.mMap.getBounds(),
			lat1 = b[0][0],
			lng1 = b[0][1],
			lat2 = b[1][0],
			lng2 = b[1][1];

		API.points.get(lat1, lng1, lat2, lng2, 0 /* todo */, false /* todo */).then(function(result) {
			var users = {};
			result.users.forEach(function(u) {
				u = new User(u);
				users[u.getId()] = u;
			});
			result["items"] = result.items.map(function(p) {
				p["author"] = users[p.ownerId];
				return new Place(p);
			});
			Main.fire(EventCode.POINT_LIST_UPDATED, {count: result.count, items: result.items});
		});
	},

	/**
	 * Вывод меток на карту
	 * @param {{count: int, items: Place[]}} data
	 */
	showPoints: function(data) {
		this.mPoints.removeAll();
		data.items.map(function(item) {
			var pl;
			Map.mCachePoint.set(item.getId(), item);
			Map.mCachePointGeoObject.set(item.getId(), pl = item.getPlacemark());
			return pl;
		}).forEach(this.mPoints.add.bind(this.mPoints));
	},


	/**
	 * Открытие плашки с информацией о метке
	 * @param {{point: Point, placemark: ymaps.GeoObject}} args
	 */
	showPointInfo: function(args) {
		Info.setContent(Points.getInfoWidget(args.point)).open();
		Map.setAddressByLocation();
	},

	event: {

		/**
		 * Вызывается при создании метки (а именно, при клике и открытии окна)
		 * @param {{lat: float, lng: float, id: int?}} args
		 */
		onCreate: function(args) {
			args.id = 0;
			return Points.showEditForm(new Point(args));
		},

		/**
		 * Вызывается после создания метки на карте (уже после запроса на сохранение)
		 * @param {{point: Point}} args
		 */
		onCreated: function(args) {
			Map.requestPointsByBounds();
		},

		/**
		 * Вызывается после редактирования метки (уже после запроса на сохранение)
		 */
		onEdited: function() {

		},

		/**
		 * Вызывается при выборе пункта перемещения метки
		 * @param {{point: Point}} args
		 */
		onMove: function(args) {
			var go = Map.mCachePointGeoObject.get(args.point.getId());
console.log(go);
			if (!go) {
				return;
			}

			go.options.set({ draggable: true });

			go.events.once("dragend", function() {
				go.options.set({ draggable: false });
				var l = go.geometry.getCoordinates();

				Main.fire(EventCode.POINT_MOVED, {point: args.point, lat: l[0], lng: l[1]});
			});
		},

		/**
		 * Вызывается после удаления
		 * @param {{point: Point}} args
		 */
		onRemove: function(args) {
			var pointId = args.point.getId(), place;

			place = Map.mCachePointGeoObject.get(pointId);

			place && place.getMap() && place.geoObjects.remove(place);

			Map.mCachePoint.set(pointId, null);
			Map.mCachePointGeoObject.set(pointId, null);
		}

	},

	utils: {
		/**
		 * Геокодирование: получение человеко-понятного адреса по координатам
		 * @param {float} lat
		 * @param {float} lng
		 * @returns {Promise.<string>}
		 */
		geocode: function(lat, lng) {
			return new Promise(function(resolve, reject) {
				ymaps.geocode([lat, lng], {kind: "street"}).then(function(data) {
					resolve(data.geoObjects.get(0).properties.get("text"));
				}, function(error) {
					reject(error);
				})
			});
		},
	}

};