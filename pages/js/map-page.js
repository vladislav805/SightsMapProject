function initMarks(bmap, marks) {
	var assoc = {};
	var listBoxItems = marks.items.map(function(mark) {
		assoc[mark.title] = mark.markId;
		return new ymaps.control.ListBoxItem({
			data: { content: mark.title },
			state: { selected: true }
		});
	});
	var nm = 10, bs = 28;
	var listBoxControl = new ymaps.control.ListBox({
		data: {
			content: "Фильтр"
		},
		items: listBoxItems,
		state: {
			expanded: false,
			filters: listBoxItems.reduce(function(filters, filter) {
				filters[filter.data.get("content")] = filter.isSelected();
				return filters;
			}, {})
		},
		options: {
			float: "none",
			position: {
				top: nm * 2 + bs,
				left: nm
			},
			size: "small"
		}
	});

	var yaMap = bmap.getMap();

	yaMap.controls.add(listBoxControl);

	var old = yaMap.controls.get("zoomControl").options.getAll();
	yaMap.controls.get("zoomControl").options.set({position: {top: old.position.top + nm + bs, left: nm}});

	// Добавим отслеживание изменения признака, выбран ли пункт списка.
	listBoxControl.events.add(["select", "deselect"], function(e) {
		var listBoxItem = e.get("target");
		var filters = ymaps.util.extend({}, listBoxControl.state.get("filters"));
		filters[listBoxItem.data.get("content")] = listBoxItem.isSelected();
		listBoxControl.state.set("filters", filters);
	});

	var filterMonitor = new ymaps.Monitor(listBoxControl.state);
	filterMonitor.add("filters", function(filters) {
		var arr = [];
		for (var key in filters) {
			if (filters.hasOwnProperty(key) && filters[key]) {
				arr.push(assoc[key]);
			}
		}

		bmap.getCollection("sights").setFilter(function(obj) {
			return hasAtLeastOne(obj.properties.sight.markIds, arr)
		});
	});
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
				/*balloonContentHeader: "{{ properties.sight.title }}",
				balloonContentBody: object.description,
				balloonContentFooter: object.pointId,*/
				//balloonPanelMaxMapArea: 0,
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
					initMarks(this, res);
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
