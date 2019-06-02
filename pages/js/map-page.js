var KEY_MARKS_SELECTED = "mapSelectedMarks";
var KEY_VISIT_STATE_SELECTED = "mapSelectedVisitState";
var KEY_SELECTED_VERIFIED = "mapSelectedVerified";
var KEY_SELECTED_MODE = "mapSelectedMode";

const VALUE_MODE_EDITOR = "editor";
const VALUE_MODE_TRAVELER = "traveler";
//var KEY_SELECTED_ARCHIVED = "mapSelectedArchived";

function initFilters(bmap, marks) {
	const YMap = bmap.getMap();

	var old = YMap.controls.get("zoomControl").options.get("position");
	YMap.controls.get("zoomControl").options.set({
		position: {
			top: old.top + BaseMap.CONTROLS_MARGIN * 4 + BaseMap.CONTROLS_SIZE * 4,
			left: BaseMap.CONTROLS_MARGIN
		}
	});

	const dMarks = getListBoxMarks(marks);
	const dVisitState = getListBoxVisitState();
	const dVerified = getButtonVerified();
	const dMode = getMapModeBox();

	const lbMarks = dMarks.listBox;
	const lbVisitState = dVisitState.listBox;
	const bVerified = dVerified.button;
	const lbMode = dMode.listBox;

	const slMarks = dMarks.selected;
	const slVisitState = dVisitState.selected;
	let sVerified = dVerified.selected;

	bmap.addControl(lbMarks);
	bmap.addControl(lbVisitState);
	bmap.addControl(bVerified);
	bmap.addControl(lbMode);

	lbMarks.events.add(["select", "deselect"], function(e) {
		var markId = e.get("target") && e.get("target").data && e.get("target").data.get("markId");

		switch (e.get("type")) {
			case "select":
				slMarks.push(markId);
				break;

			case "deselect":
				slMarks.splice(slMarks.indexOf(markId), 1);
				break;
		}

		storage.set(KEY_MARKS_SELECTED, slMarks);
		lbMarks.state.set("filters", slMarks.slice(0)); // bug ymaps: not filtering if equals array pointer
	});

	lbVisitState.events.add(["select", "deselect"], function(e) {
		var vs = e.get("target") && e.get("target").data && e.get("target").data.get("state");

		switch (e.get("type")) {
			case "select":
				slVisitState.push(vs);
				break;

			case "deselect":
				slVisitState.splice(slVisitState.indexOf(vs), 1);
				break;
		}

		storage.set(KEY_VISIT_STATE_SELECTED, slVisitState);
		lbVisitState.state.set("filters", slVisitState.slice(0)); // bug ymaps: not filtering if equals array pointer
	});

	bVerified.events.add(["select", "deselect"], function(e) {
		sVerified = bVerified.state.get("selected");
		storage.set(KEY_SELECTED_VERIFIED, sVerified);
		console.log(sVerified);
		bVerified.state.set("filters", sVerified);
	});

	lbMode.events.add(["select"], function(e) {
		lbMode.state.set("filters", dMode.getValue());
	});

	const filter = function(obj) {
		return filterByMarksAndVisitStateAndVerified(obj, slMarks, slVisitState, sVerified, dMode.getValue());
	};

	bmap.getCollection("sights").setFilter(filter);

	var onMonitorFired = function(filters) {
		bmap.getCollection("sights").setFilter(filter.bind(null));
	};

	new ymaps.Monitor(lbMarks.state).add("filters", onMonitorFired);
	new ymaps.Monitor(lbVisitState.state).add("filters", onMonitorFired);
	new ymaps.Monitor(bVerified.state).add("filters", onMonitorFired);
	new ymaps.Monitor(lbMode.state).add("filters", onMonitorFired);
}

/**
 * Функция для фильтрации мест
 * @param {{properties: {sight: API.Sight}, options: object}} object
 * @param {int[]} marks
 * @param {int[]} visitState
 * @param {boolean} onlyVerified
 * @param {string} mode
 * @returns {boolean}
 */
function filterByMarksAndVisitStateAndVerified(object, marks, visitState, onlyVerified, mode) {
	let icon = null;

	// В зависимости от режима просмотра икони достопримечательностей окрашиваются в разные цветовые схемы
	switch (mode) {

		// Если это режим редактора, то...
		case VALUE_MODE_EDITOR:
			switch (true) {
				// Архивные отображаются серым
				case object.properties.sight.isArchived: icon = "islands#grayDotIcon"; break;

				// Верифицированные отображаются синим
				case object.properties.sight.isVerified: icon = "islands#blueDotIcon"; break;

				// Неизвестные отображаются чёрным
				default: icon = "islands#blackDotIcon";
			}
			break;

		// Если это режим путешественника, то...
		case VALUE_MODE_TRAVELER:
			// Архивные не показываем, ибо уже смотреть нечего
			if (object.properties.sight.isArchived) {
				return false;
			}

			switch (object.properties.sight.visitState) {
				// Посещенные показываем зеленым
				case API.sights.visitState.VISITED: icon = "islands#greenDotIcon"; break;

				// Желаемые показываем красным
				case API.sights.visitState.DESIRED: icon = "islands#redDotIcon"; break;

				// Остальные синие
				default:
					if (object.properties.sight.isVerified) {
						icon = "islands#blueDotIcon";
					} else {
						icon = "islands#blueIcon";
					}
			}
	}
	// Если иконка выбрана, меняем её
	if (icon !== null) {
		object.options.preset = icon;
	}

	//
	if (onlyVerified && !object.properties.sight.isVerified) {
		return false;
	}

	if (!object.properties.sight.markIds.length) {
		return true;
	}

	return hasAtLeastOne(object.properties.sight.markIds, marks) && visitState.indexOf(object.properties.sight.visitState) >= 0;
}

/**
 *
 * @returns {{listBox: ymaps.control.ListBox, selected: int, getValue: function()}}
 */
function getMapModeBox() {
	const selectedMode = KEY_SELECTED_MODE in localStorage
		? localStorage[KEY_SELECTED_MODE]
		: VALUE_MODE_TRAVELER;

	const listItemEditor = new ymaps.control.ListBoxItem({
		options: { selectOnClick: false },
		data: { content: "Редактор", value: VALUE_MODE_EDITOR },
		state: { selected: selectedMode === VALUE_MODE_EDITOR }
	});
	const listItemTraveler = new ymaps.control.ListBoxItem({
		options: { selectOnClick: false },
		data: { content: "Путешественник", value: VALUE_MODE_TRAVELER },
		state: { selected: selectedMode === VALUE_MODE_TRAVELER }
	});

	let mMode = selectedMode;

	const lb = new ymaps.control.ListBox({
		data: { content: "Режим просмотра" },
		items: [ listItemEditor, listItemTraveler ],
		state: { expanded: false },
		options: {
			float: "none",
			position: {
				top: BaseMap.CONTROLS_MARGIN * 5 + BaseMap.CONTROLS_SIZE * 4,
				left: BaseMap.CONTROLS_MARGIN
			},
			size: "small"
		}
	});

	lb.events.add(["click"], function(e) {
		mMode = e.get("target") && e.get("target").data && e.get("target").data.get("value");
		if (!mMode) {
			return;
		}

		localStorage[KEY_SELECTED_MODE] = mMode;

		switch (mMode) {
			case VALUE_MODE_TRAVELER:
				listItemEditor.deselect();
				listItemTraveler.select();
				break;

			case VALUE_MODE_EDITOR:
				listItemTraveler.deselect();
				listItemEditor.select();
				break;
		}
		lb.collapse();
	});

	return {
		listBox: lb,
		selected: selectedMode,
		getValue: function() {
			return mMode;
		}
	};
}

/**
 *
 * @param marks
 * @returns {{listBox: ymaps.control.ListBox, selected: int[]}}
 */
function getListBoxMarks(marks) {
	var selectedMarks = storage.has(KEY_MARKS_SELECTED)
		? storage.get(KEY_MARKS_SELECTED)
		: marks.items.map(function(m) { return m.markId; });

	return {
		listBox: new ymaps.control.ListBox({
			data: { content: "Категории" },
			items: marks.items.map(function(mark) {
				return new ymaps.control.ListBoxItem({
					data: { content: mark.title, markId: mark.markId },
					state: { selected: ~selectedMarks.indexOf(mark.markId) }
				});
			}),
			state: { expanded: false },
			options: {
				float: "none",
				position: {
					top: BaseMap.CONTROLS_MARGIN * 2 + BaseMap.CONTROLS_SIZE,
					left: BaseMap.CONTROLS_MARGIN
				},
				size: "small"
			}
		}),
		selected: selectedMarks
	};
}

/**
 *
 * @returns {{listBox: ymaps.control.ListBox, selected: int[]}}
 */
function getListBoxVisitState() {
	var selectedStates = storage.has(KEY_VISIT_STATE_SELECTED)
		? storage.get(KEY_VISIT_STATE_SELECTED)
		: [0, 1, 2];

	return {
		listBox: new ymaps.control.ListBox({
			data: { content: "Посещенность" },
			items: [ "Непосещенные", "Посещенные", "Желаемые" ].map(function(label, index) {
				return new ymaps.control.ListBoxItem({
					data: { content: label, state: index },
					state: { selected: ~selectedStates.indexOf(index) }
				});
			}),
			state: { expanded: false },
			options: {
				float: "none",
				position: {
					top: BaseMap.CONTROLS_MARGIN * 3 + BaseMap.CONTROLS_SIZE * 2,
					left: BaseMap.CONTROLS_MARGIN
				},
				size: "small"
			}
		}),
		selected: selectedStates
	}
}

function getButtonVerified() {
	var isSelected = storage.has(KEY_SELECTED_VERIFIED)
		? storage.get(KEY_SELECTED_VERIFIED)
		: true;

	return {
		button: new ymaps.control.Button({
			data: {
				content: "Подтвержденное",
				title: "Если активно, то показываются только те места, которые подтвердили модераторы или другие пользователи"
			},

			state: { selected: isSelected },
			options: {
				float: "none",
				position: {
					top: BaseMap.CONTROLS_MARGIN * 4 + BaseMap.CONTROLS_SIZE * 3,
					left: BaseMap.CONTROLS_MARGIN
				},
				maxWidth: 200
			}
		}),
		selected: isSelected
	}
}

/**
 *
 * @param {int[]} source
 * @param {int[]} dest
 * @returns {boolean}
 */
function hasAtLeastOne(source, dest) {
	for (var i = 0, l = dest.length; i < l; ++i) {
		if (~source.indexOf(dest[i])) {
			return true;
		}
	}
	return false;
}

/**
 * @param {BaseMap} bmap
 * @param {float[][]} c
 */
function checkoutSightsInBounds(bmap, c) {
	API.sights.get(c[0][0], c[0][1], c[1][0], c[1][1]).then(function(data) {
		(data.type === "cities" ? showCities : showSights)(bmap, data.items);
	});
}

function showCities(bmap, cities) {

	bmap.removeCollection("sights");
	bmap.restoreCollection("cities");

	var collection = bmap.getCollection("cities");

	cities.forEach(function(city) {
		collection.add(getInstancePlacemark(city));
	});
}

function showSights(bmap, sights) {

	bmap.removeCollection("cities");
	bmap.restoreCollection("sights");


	var collection = bmap.getCollection("sights");

	collection.add(sights.map(function(sight) {
		return getInstancePlacemark(sight);
	}));
}

window.__marks = [];
window.__placemarks = {};

/**
 * @param {API.StandaloneCity|API.Sight} object
 */
function getInstancePlacemark(object) {
	var plId = object instanceof API.Sight ? object.sightId : -object.cityId;

	if (window.__placemarks[plId]) {
		return window.__placemarks[plId];
	}

	var pl;

	if (object instanceof API.Sight) {
		pl = {
			type: "Feature",
			id: object.sightId,
			geometry: {
				type: "Point",
				coordinates: [object.lat, object.lng]
			},
			options: {
				hintLayout: SightHintLayout,
				balloonContentLayout: SightBalloonContentLayout,
			},
			properties: {
				sight: object
			}
		};

		pl.options.preset = "islands#blueDotIcon";
	} else {
		pl = new ymaps.Placemark([object.lat, object.lng], {
			iconContent: String(object.count),
			iconCaption: object.name
		});
	}

	return window.__placemarks[plId] = pl;
}


var SightBalloonContentLayout, SightHintLayout;


window.MapPage = (function() {

	let initialInitialization = false;

	const initializeBaseBlocks = () => {
		SightBalloonContentLayout = ymaps.templateLayoutFactory.createClass([
			"<div class=\"map-balloon-wrap {% if (properties.sight.isArchived) %}map-balloon--archived{% endif %} {% if (properties.sight.isVerified) %}map-balloon--verified{% endif %}\">",
			"<strong><a href=\"/sight/{{properties.sight.sightId}}\" target=\"_blank\">{{properties.sight.title}}</a></strong>",
			"<p>{{properties.sight.description}}</p>",
			"<time>#{{ properties.sight.sightId }}, {{ properties.sight.dateCreated | fullDate }}</time>",
			"</div>"
		].join(""), {
			/**
			 * Переопределяем функцию build, чтобы при создании макета начинать
			 * слушать событие click на кнопке-счетчике.
			 */
			build: function() {
				// Сначала вызываем метод build родительского класса.
				SightBalloonContentLayout.superclass.build.call(this);

				// ...
			},

			/**
			 * Аналогично переопределяем функцию clear, чтобы снять
			 * прослушивание клика при удалении макета с карты.
			 */
			clear: function() {
				// Выполняем действия в обратном порядке - сначала снимаем слушателя,
				// а потом вызываем метод clear родительского класса.
				// ...
				SightBalloonContentLayout.superclass.clear.call(this);
			}
		});

		//noinspection JSUnusedGlobalSymbols
		SightHintLayout = ymaps.templateLayoutFactory.createClass([
				"<div class=\"map-hint-wrap {% if (properties.sight.isArchived) %}map-hint--archived{% endif %} {% if (properties.sight.isVerified) %}map-hint--verified{% endif %}\">",
				"<strong><a href=\"/sight/{{ properties.sight.sightId }}\" target=\"_blank\">{{ properties.sight.title }} <i class='material-icons'></i></a></strong>",
				// "<div>{{ properties.sight.markIds | listMarks }}</div>",
				"<time>#{{ properties.sight.sightId }}, {{ properties.sight.dateCreated | fullDate }}</time>",
				"</div>"].join(""), {
				getShape: function () {
					var el = this.getElement(),
						result = null;
					if (el) {
						var firstChild = el.firstChild;
						result = new ymaps.shape.Rectangle(
							new ymaps.geometry.pixel.Rectangle([
								[0, 0],
								[firstChild.offsetWidth, firstChild.offsetHeight]
							])
						);
					}
					return result;
				}
			}
		);

		ymaps.template.filtersStorage.add("fullDate", (dataManager, text, filterValue) => new Date(text * 1000).format(BaseMap.DEFAULT_FULL_DATE_FORMAT));
		// ymaps.template.filtersStorage.add("listMarks", (dataManager, text, filterValue) => text.join(", "));
	};

	return {

		init: function() {
			ymaps.ready(function() {

				if (!initialInitialization) {
					initializeBaseBlocks();
				}

				new BaseMap(ge("map"), null, {
					updateAddressOnChange: true,

					/**
					 * @param {ymaps.Map} yMap
					 */
					onMapReady: function(yMap) {

						setTimeout(() => yMap.container.fitToViewport(), 400);

						const citiesCollection = new ymaps.GeoObjectCollection(null, {
							preset: "islands#blueCircleIcon",
							strokeWidth: 10
						});

						const sightsCollection = new ymaps.ObjectManager({
							gridSize: 80,
							clusterize: true,
							clusterOpenBalloonOnClick: false,
							groupByCoordinates: false
						});

						citiesCollection.events.add(["click"], function(e) {
							var cityMark = e.get("target");
							yMap.panTo(cityMark.geometry.getCoordinates(), {
								checkZoomRange: true,
							}).then(function () {
								yMap.setZoom(12, {
									checkZoomRange: true,
									duration: 200
								})
							});
						});

						yMap.events.add("boundschange", function(e) {
							const z = e.get("newZoom") < 19;
							sightsCollection.options.set({
								clusterize: z
							});
						});

						sightsCollection.objects.options.set({
							preset: "islands#blueIcon"
						});

						this.addCollection("cities", citiesCollection);
						this.addCollection("sights", sightsCollection);


						API.marks.get().then(res => initFilters(this, res));

						checkoutSightsInBounds(this, yMap.getBounds());
					},

					/**
					 * @param {{tl: {lat: float, lng: float}, br: {lat: float, lng: float}}} c
					 */
					onBoundsChanged: function(c) {
						checkoutSightsInBounds(this, [[c.tl.lat, c.tl.lng], [c.br.lat, c.br.lng]]);
					}
				});
			});
		}
	};
})();
