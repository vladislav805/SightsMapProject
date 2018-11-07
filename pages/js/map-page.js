var KEY_MARKS_SELECTED = "mapSelectedMarks";
var KEY_VISIT_STATE_SELECTED = "mapSelectedVisitState";

function initFilters(bmap, marks) {
	var old = bmap.getMap().controls.get("zoomControl").options.get("position");
	bmap.getMap().controls.get("zoomControl").options.set({
		position: {
			top: old.top + BaseMap.CONTROLS_MARGIN * 2 + BaseMap.CONTROLS_SIZE * 2,
			left: BaseMap.CONTROLS_MARGIN
		}
	});

	var dMarks = getListBoxMarks(marks);
	var dVisitState = getListBoxVisitState();

	var lbMarks = dMarks.listBox;
	var lbVisitState = dVisitState.listBox;

	var slMarks = dMarks.selected;
	var slVisitState = dVisitState.selected;

	bmap.addControl(lbMarks);
	bmap.addControl(lbVisitState);

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

	bmap.getCollection("sights").setFilter(function(obj) {
		return filterByMarksAndVisitState(obj, slMarks, slVisitState);
	});

	var onMonitorFired = function(filters) {
		bmap.getCollection("sights").setFilter(function(obj) {
			return filterByMarksAndVisitState(obj, slMarks, slVisitState);
		});
	};

	new ymaps.Monitor(lbMarks.state).add("filters", onMonitorFired);
	new ymaps.Monitor(lbVisitState.state).add("filters", onMonitorFired);
}

function filterByMarksAndVisitState(object, marks, visitState) {
	return hasAtLeastOne(object.properties.sight.markIds, marks) && ~visitState.indexOf(object.properties.sight.visitState);
}

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
	API.points.get(c[0][0], c[0][1], c[1][0], c[1][1]).then(function(data) {
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
	var plId = object instanceof API.Sight ? object.pointId : -object.cityId;

	if (window.__placemarks[plId]) {
		return window.__placemarks[plId];
	}

	var pl;

	if (object instanceof API.Sight) {
		pl =  {
			type: "Feature",
			id: object.pointId,
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
		if (object.isArchived) {
			pl.options.preset = "islands#grayIcon";
		}


	} else {
		pl = new ymaps.Placemark([object.lat, object.lng], {
			iconContent: String(object.count),
			iconCaption: object.name
		});
	}

	return window.__placemarks[plId] = pl;
}


var SightBalloonContentLayout, SightHintLayout;


window.addEventListener("load", function() {
	Sugar.Date.extend();
	Sugar.Object.extend();
	Sugar.String.extend();
	Sugar.Number.extend();

	ymaps.ready(function() {
		new BaseMap(ge("map"), null, {
			updateAddressOnChange: true,

			/**
			 * @param {ymaps.Map} yMap
			 */
			onMapReady: function(yMap) {
				var citiesCollection = new ymaps.GeoObjectCollection(null, {
					preset: "islands#blueCircleIcon",
					strokeWidth: 10
				});
				var sightsCollection = new ymaps.ObjectManager({
					gridSize: 80,
					clusterize: true,
					clusterOpenBalloonOnClick: false,
					groupByCoordinates: false
				});

				citiesCollection.events.add(["click"], function(e) {
					var cityMark = e.get("target");
					yMap.panTo(cityMark.geometry.getCoordinates(), {
						checkZoomRange: true,
					}).then(function() {
						yMap.setZoom(12, {
							checkZoomRange: true,
							duration: 200
						})
					});
				});

				sightsCollection.objects.options.set({
					preset: "islands#blueIcon"
				});

				this.addCollection("cities", citiesCollection);
				this.addCollection("sights", sightsCollection);


				API.marks.get().then(function(res) {
					initFilters(this, res);
				}.bind(this));

				checkoutSightsInBounds(this, yMap.getBounds());
			},

			/**
			 * @param {{tl: {lat: float, lng: float}, br: {lat: float, lng: float}}} c
			 */
			onBoundsChanged: function(c) {
				checkoutSightsInBounds(this, [[c.tl.lat, c.tl.lng], [c.br.lat, c.br.lng]]);
			}
		});

		SightBalloonContentLayout = ymaps.templateLayoutFactory.createClass([
			"<div class=\"map-balloon-wrap {% if (properties.sight.isArchived) %}map-balloon--archived{% endif %} {% if (properties.sight.isVerified) %}map-balloon--verified{% endif %}\">",
				"<strong><a href=\"/place/{{properties.sight.pointId}}\" target=\"_blank\">{{properties.sight.title}}</a></strong>",
				"<p>{{properties.sight.description}}</p>",
				"<time>#{{ properties.sight.pointId }}, {{ properties.sight.dateCreated | fullDate }}</time>",
			"</div>"
		].join(""), {
			/**
			 * Переопределяем функцию build, чтобы при создании макета начинать
			 * слушать событие click на кнопке-счетчике.
             */
			build: function () {
				// Сначала вызываем метод build родительского класса.
				SightBalloonContentLayout.superclass.build.call(this);

				// ...
			},

			/**
			 * Аналогично переопределяем функцию clear, чтобы снять
			 * прослушивание клика при удалении макета с карты.
			 */
			clear: function () {
				// Выполняем действия в обратном порядке - сначала снимаем слушателя,
				// а потом вызываем метод clear родительского класса.
				// ...
				SightBalloonContentLayout.superclass.clear.call(this);
			}
		});

		//noinspection JSUnusedGlobalSymbols
		SightHintLayout = ymaps.templateLayoutFactory.createClass([
			"<div class=\"map-hint-wrap {% if (properties.sight.isArchived) %}map-hint--archived{% endif %} {% if (properties.sight.isVerified) %}map-hint--verified{% endif %}\">",
				"<strong><a href=\"/place/{{ properties.sight.pointId }}\" target=\"_blank\">{{ properties.sight.title }} <i class='material-icons'></a></strong>",
				"<time>#{{ properties.sight.pointId }}, {{ properties.sight.dateCreated | fullDate }}</time>",
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


		ymaps.template.filtersStorage.add('fullDate', function(dataManager, text, filterValue) {
			return new Date(text * 1000).format(BaseMap.DEFAULT_FULL_DATE_FORMAT);
		});
	});
});
