var Map = {

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

	/**
	 * Инициализация карты
	 */
	init: function() {
		if (this.mMap) {
			return;
		}

		this.mCachePoint = new Bundle;
		this.mCachePointGeoObject = new Bundle;
		this.mFilter = new FilterMap;

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

		this.mMap.geoObjects.add(this.mPoints = new ymaps.Clusterer({
			gridSize: 80
		}));

		/**
		 * Подвеска событий
		 */
		this.mMap.events.add("boundschange", function() {
			this.mInitedPlace && Main.fire(EventCode.MAP_BOUNDS_CHANGED);
		}.bind(this));
		this.mMap.events.add("click", function(event) {

			if (this.mMap.balloon.isOpen()) {
				this.mMap.balloon.close();
				return;
			}

			if (isCurrentAsideOpenPointInfo()) {
				Aside.pop();
			} else {
				var c = event.get("coords");
				Main.fire(EventCode.POINT_CREATE, {
					lat: c[0],
					lng: c[1]
				});
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
				placeholderContent: "Поиск по адресу на карте",
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
		d.lat && d.lng && Map.__setInitialLocation(parseFloat(d.lat), parseFloat(d.lng), Map.mMap.getZoom());
		d.z && Map.mMap.setZoom(parseFloat(d.z));
	},

	/**
	 * Обновление параметров в адресной строке по текущему положению карты
	 */
	setAddressByLocation: function() {
		var c = this.mMap.getCenter(),
			pointId = Aside.getLast() && Aside.getLast().getData() && Aside.getLast().getData().pointId,
			url = {lat: c[0].toFixed(6), lng: c[1].toFixed(6), t: Map.mMap.getType().split("#")[1], z: Map.mMap.getZoom()};

		pointId && (url.id = pointId);

		history.replaceState(null, "", "?" + Sugar.Object.toQueryString(url));
	},

	/**
	 * Установка положения карты по последним данным
	 */
	setLocationByLastPosition: function() {
		Map.__setInitialLocation(storage.get(Const.LAST_LAT), storage.get(Const.LAST_LNG), storage.get(Const.LAST_ZOOM));
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
			var c = result.geoObjects.position;
			Map.__setInitialLocation(c[0], c[1], 10);
		});
	},

	initFilters: function() {
		var f = new Select(g("mapOptionVisited")),
			createCallback = function(state) {
				/**
				 * @param {{item: HTMLElement, id: *, instance: SelectItem}}
				 */
				return function(opts) {
					Main.fire(EventCode.MAP_FILTER_UPDATED, {visitState: opts.id});
					opts.instance.getParent().getNodeValue().textContent = opts.instance.getTitle();
				};
			};
		f.add(new SelectItem("Все", -1, createCallback(-1)));
		f.add(new SelectItem("Не посещенные", 0, createCallback(0)));
		f.add(new SelectItem("Посещенные", 1, createCallback(1)));
		f.add(new SelectItem("Желаемые", 2, createCallback(2)));
	},

	/**
	 *
	 */
	setInitialStateMap: function() {
		var g = get();
		if (g.lat && g.lng) {
			Map.setLocationByAddress(g.lat, g.lng, g.z);
			Map.requestPointsByBounds();
		} else {
			if (g.id && !g.lat && !g.lng) {
				var pointId = parseInt(g.id);
				API.points.getById(pointId).then(function(point) {
					Map.__setInitialLocation(point.lat, point.lng, 18);

					var onUpdatedOpenInfo = function(res) {
						Main.removeListener(EventCode.POINT_LIST_UPDATED, onUpdatedOpenInfo);
						for (var i = 0, l = res.items.length; i < l; ++i) {
							if (res.items[i].getInfo().getId() === pointId) {
								Main.fire(EventCode.POINT_CLICK, {point: res.items[i].getInfo()});
								break;
							}
						}
					};

					Main.addListener(EventCode.POINT_LIST_UPDATED, onUpdatedOpenInfo);
				});
			} else if (storage.get(Const.LAST_LAT) && storage.get(Const.LAST_LNG)) {
				Map.setLocationByLastPosition();
			} else {
				Map.setLocationByGeolocation();
			}
		}
	},

	__setInitialLocation: function(lat, lng, z) {
		Map.mInitedPlace = true;
		Map.mMap && Map.mMap.setCenter([lat, lng], z);
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

		API.points.get(
			lat1, lng1, lat2, lng2,
			this.mFilter.getMarkIds(),
			this.mFilter.getOnlyVerified(),
			this.mFilter.getVisitState()
		).then(function(result) {
			var users = {};
			result.users.forEach(function(u) {
				u = User.get(u);
				users[u.getId()] = u;
			});

			if (~Map.mFilter.getVisitState()) {
				result["items"] = result["items"].filter(function(point) {
					return point.visitState === Map.mFilter.getVisitState();
				});
			}

			if (Map.mFilter.getMarkIds().length && Map.mFilter.getMarkIds().length !== Marks.getItems().length) {
				var fl = Map.mFilter.getMarkIds();
				result["items"] = result["items"].filter(function(point) {

					for (var i = 0, l; l = fl[i]; ++i) {
						if (~point.markIds.indexOf(l)) {
							return true;
						}
					}

					return false;
				});
			}

			result["items"] = result.items.map(function(p) {
				p["author"] = users[p.ownerId];
				return Place.get(p);
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
	 * @param {{point: Point}} args
	 */
	showPointInfo: function(args) {
		if (!args.point) {
			return;
		}
		//Aside.getLast() && Aside.getLast().getData() && Aside.getLast().getData().pointId && Aside.pop();
		isCurrentAsideOpenPointInfo() && Aside.pop();
		Aside.push(Points.getInfoWidget(args.point));
		Map.setAddressByLocation();
	},

	event: {

		/**
		 * Вызывается при смене параметров вывода меток на карту
		 * @param {{markIds: int[]=, onlyVerified: boolean=, visitState: int=}} args
		 */
		onFilterUpdated: function(args) {
			"markIds" in args && Map.mFilter.setMarkIds(args.markIds);
			"onlyVerified" in args && Map.mFilter.setOnlyVerified(args.onlyVerified);
			"visitState" in args && Map.mFilter.setVisitState(args.visitState);
			Map.requestPointsByBounds();
		},

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