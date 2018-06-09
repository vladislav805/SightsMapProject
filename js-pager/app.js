var XMap = {

	COORD_GLUE: "_",

	LAST_LAT: "lastLat",
	LAST_LNG: "lastLng",
	LAST_ZOOM: "lastZoom",

	DEFAULT_FULL_DATE_FORMAT: "%d/%m/%Y %H:%M",

	/**
	 * @var {ymaps.Map}
	 */
	mMap: null,

	/**
	 * @var {ymaps.Clusterer}
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
	 * @var {FilterMap}
	 */
	mFilter: null,

	mInitedPlace: false,


	mFilters: {
		visit: -1,
		verify: -1,
	},

	/**
	 * Инициализация карты
	 */
	init: function() {
		if (this.mMap) {
			return;
		}

		var ui = document.querySelectorAll(".mm-fd"); // m-map form dynamic

		var UICode = {
			VERIFIED_STATE: "mm-verified",
			VISIT_STATE: "mm-state"
		};

		var ftrs = this.mFilters;

		var update = function(node) {
			switch (node.name) {
				case UICode.VISIT_STATE:
					ftrs.visit = parseInt(node.value);
					break;

				case UICode.VERIFIED_STATE:
					ftrs.verify = parseInt(node.value);
					break;
			}
		};

		Array.prototype.forEach.call(ui, function(node) {
			node.addEventListener("change", function(event) {
				update(node);
				XMap.refilter();
			});

			update(node);
		});

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

		this.mMap.geoObjects.add(this.mPoints = new ymaps.ObjectManager({
			gridSize: 80,
			clusterize: true,
//			geoObjectOpenBalloonOnClick: false,
//			clusterOpenBalloonOnClick: false,
			preset: "islands#darkBlueClusterIcons"
		}));


		/**
		 * Подвеска событий
		 */
		this.mMap.events.add("boundschange", function() {
			XMap.setAddressByLocation();
			XMap.requestPointsByBounds();
			XMap.savePosition();
		}.bind(this));

		this.mMap.events.add("click", function(event) {
			if (this.mMap.balloon.isOpen()) {
				this.mMap.balloon.close();
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
				placeholderContent: "Поиск по адресу",
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

		/**
		 * Карта готова
		 */
		this.setInitialStateMap();
	},


	setInitialStateMap: function() {
		var g = get(), coord;

		if ((coord = g.c) && (coord = coord.split(this.COORD_GLUE)).length === 2) {
			XMap.setLocationByAddress(parseFloat(coord[0]), parseFloat(coord[1]), parseFloat(g.z));
		} else if (storage.get(this.LAST_LAT) && storage.get(this.LAST_LNG)) {
			XMap.setLocationByLastPosition(g);
		} else {
			XMap.setLocationByGeolocation();
		}

		if (g.id) {
			var pointId = parseInt(g.id);
			API.points.getById(pointId).then(function(point) {
				console.log(point);
			});
		}
	},


	/**
	 * Установка положения карты по параметрам в адресе
	 * TODO: Зачем инициируется масштабирование отдельно?
	 */
	setLocationByAddress: function(lat, lng, z) {
		XMap.__setInitialLocation(lat, lng, XMap.mMap.getZoom());
		z && XMap.mMap.setZoom(z);
	},

	/**
	 * Обновление параметров в адресной строке по текущему положению карты
	 */
	setAddressByLocation: function() {
		var c = this.mMap.getCenter(),
			url = {c: [c[0].toFixed(6), c[1].toFixed(6)].join(this.COORD_GLUE), z: XMap.mMap.getZoom()};

		history.replaceState(null, "", "?" + Sugar.Object.toQueryString(url));
	},

	/**
	 * Установка положения карты по последним данным
	 */
	setLocationByLastPosition: function() {
		XMap.__setInitialLocation(storage.get(XMap.LAST_LAT), storage.get(XMap.LAST_LNG), storage.get(XMap.LAST_ZOOM));
	},

	/**
	 * Сохранение в локальное хранилище браузера текущего положения карты
	 */
	savePosition: function() {
		var m = this.mMap, c = m.getCenter();

		storage.set(XMap.LAST_LAT, c[0]);
		storage.set(XMap.LAST_LNG, c[1]);
		storage.set(XMap.LAST_ZOOM, m.getZoom());
	},

	setLocationByGeolocation: function() {
		ymaps.geolocation.get({
			provider: "yandex",
			mapStateAutoApply: true
		}).then(function(result) {
			console.log(result);
			var c = result.geoObjects.position;
			XMap.__setInitialLocation(c[0], c[1], 10);
		});
	},



	__setInitialLocation: function(lat, lng, z) {
		XMap.mInitedPlace = true;
		XMap.mMap && XMap.mMap.setCenter([lat, lng], z);
	},

	__latestResult: null,

	/**
	 * Запрос данных об указанном участке карты. Вызывается после того, как карта
	 * была сдвинута пользователем или программно.
	 * После получения и обработки данных вызывается событие POINT_LIST_UPDATED с объектом {count: int, items: Place[]}
	 */
	requestPointsByBounds: function() {
		var b = XMap.mMap.getBounds(),
			lat1 = b[0][0],
			lng1 = b[0][1],
			lat2 = b[1][0],
			lng2 = b[1][1];

		API.points.get(
			lat1, lng1, lat2, lng2
		).then(function(result) {
			XMap.showPoints(this.__latestResult = this.filterItems(result.items));
		}.bind(this));
	},

	refilter: function() {
		if (!this.__latestResult) {
			return;
		}
		this.mPoints.removeAll();
		this.showPoints(this.filterItems(this.__latestResult))
	},

	filterItems: function(items) {
		return items.filter(function(point) {
			return XMap.mFilters.visit < 0 || XMap.mFilters.visit >= 0 && XMap.mFilters.visit === point.visitState;
		}).filter(function(point) {
			switch (XMap.mFilters.verify) {
				case -1: return true;
				case 0: return !point.isVerified;
				case 1: return point.isVerified;
			}
		});
	},

	/**
	 * Вывод меток на карту
	 * @param {object[]} data
	 */
	showPoints: function(data) {
		data.map(function(item) {
			this.mPoints.add({
				type: "Feature",
				id: item.pointId,
				geometry: {
					type: "Point",
					coordinates: [item.lat, item.lng]
				},
				properties: {
					iconContent: item.pointId,
					iconMaxWidth: 25,
					balloonContentHeader: "<a href='/place/" + item.pointId + "' target='_blank'>" + item.title + "</a>",
					balloonContentBody: this.makeDescription(item),
					balloonContentFooter: "#" + item.pointId + "; " + Sugar.Date.format(new Date(item.dateCreated * 1000), this.DEFAULT_FULL_DATE_FORMAT)
				},
				options: {
					preset: !item.isArchived ? "islands#blueStretchyIcon" : "islands#grayStretchyIcon",

				}
			});
		}.bind(this));
	},

	makeDescription: function(item) {
		return "<p>" + [
			item.city && "Город: " + item.city.name,
			item.description
		].filter(function(i) { return i; }).join("</p><p>") + "</p>";
	},


	/**
	 * Открытие плашки с информацией о метке
	 * @param {{point: Point}} args
	 */
	showPointInfo: function(args) {
		if (!args.point) {
			return;
		}
		//Aside.getLast() && Aside.getLast().getData() && Aside.getLast().getData().pointId && Aside.pop();
		isCurrentAsideOpenPointInfo() && Aside.pop();
		Aside.push(Points.getInfoWidget(args.point));
		XMap.setAddressByLocation();
	},

	event: {


		/**
		 * Вызывается когда требуется подсветить или погасить метку на карте (например, при наведении курсора на элемент
		 * в списке)
		 * @param {{place: Place, state: boolean}} args
		 */
		onHighlight: function(args) {
			var place = args.place,
				state = args.state,
				pm = place.getPlacemark(),
				cl = Map.mPoints.getObjectState(pm).cluster;

			if (state) {
				place.mWasColor = pm.options.get("iconColor");
				pm.options.set({iconColor: "red", zIndex: 9999999});

				if (cl) {
					place.mWasClusterColor = cl.options.get("iconColor");
					cl.options.set({iconColor: "red"});
				}
			} else {
				pm.options.set({iconColor: place.mWasColor, zIndex: place.getId()});

				cl && cl.options.set({iconColor: place.mWasClusterColor});
			}
		},

		/**
		 * Вызывается когда требуется переместить карту, где в центре будет показана метка, переданная в аргументе
		 * @param {{place: Place}} args
		 */
		onShow: function(args) {
			if (!args.place) {
				alert("Не могу найти это место");
				return;
			}
			Main.fire(EventCode.POINT_CLICK, {point: args.place.getInfo()});
			var z = Map.mMap.getZoom();

			if (z < 14) {
				z = 16;
			}

			Map.mMap && Map.mMap.setCenter(args.place.getInfo().getCoordinates(), z);
		},

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
			new Toast("Успешно сохранено, спасибо!").open(4000);
		},

		/**
		 * Вызывается после редактирования метки (уже после запроса на сохранение)
		 * @param {{point: Point}} args
		 */
		onEdited: function(args) {
			new Toast("Успешно сохранено, спасибо!").open(4000);
		},

		/**
		 * Вызывается при выборе пункта перемещения метки
		 * @param {{point: Point}} args
		 */
		onMove: function(args) {
			var go = Map.mCachePointGeoObject.get(args.point.getId());

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
		 * @param {{point: Point, toast: Toast}} args
		 */
		onRemove: function(args) {
			args.toast.setText("Успешно удалено!").open(1000);
			isCurrentAsideOpenPointInfo() && Aside.pop();

			var pointId = args.point.getId();

			Map.mCachePoint.set(pointId, null);
			Map.mCachePointGeoObject.set(pointId, null);

			Map.requestPointsByBounds();
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