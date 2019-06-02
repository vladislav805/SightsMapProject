const NeuralPage = {

	/**
	 *
	 * @param {{has: boolean=, force: boolean=, args: object=}} options
	 */
	init: function(options) {
		this.setPageState(this.STATE_LEARNING);
		if (options.has || options.force) {
			let args = options.args || {};
			API.neuralNetwork.getInterestedSights(args)
			   .then(NeuralPage.onResponse.bind(this, args))
			   .catch(NeuralPage.onError);
		} else {
			ge("neural_btn_init").addEventListener("click", () => this.learn());
			this.setPageState(this.STATE_NOT_INIT);
		}
	},

	onError: function(error) {
		console.error(error);
	},

	/**
	 *
	 * @param {{
	 *     typeMovement: string,
	 *     onlyVerified: boolean
	 * }=} args
	 * @param {{
	 *     count: int,
	 *     error: int,
	 *     items: API.Sight[],
	 *     clusters: {id: int, items: int[]}[]
	 * }} data
	 */
	onResponse: function(args, data) {
		const was = [];

		const wrap = ge("neural_list");
		emptyNode(wrap);
		wrap.appendChild(ce("button", {
			onclick: event => NeuralPage.openModalRouteSettings()
		}, null, "Задать параметры"));
		wrap.appendChild(NeuralPage.__getRoutes(was, data.clusters, data.items));
		if (!args || !args.typeMovement) {
			wrap.appendChild(NeuralPage.__getList(was, data.items));
		}
		this.__initButtonOpenRouteMap();
		NeuralPage.setPageState(this.STATE_DONE);
	},

	learn: function() {
		this.setPageState(this.STATE_LEARNING);
		this.init({force: true});
	},

	STATE_NOT_INIT: "not_init",
	STATE_LEARNING: "learning",
	STATE_DONE: "done",
	STATE_ERROR: "error",
	STATE_SETUP: "setup",

	setPageState: function(state) {
		ge("neural_wrap").dataset.state = state;
	},

	/**
	 *
	 * @param {int[]} was
	 * @param {{id: int, items: int[]}[]} clusters
	 * @param {API.Sight[]} sights
	 * @private
	 */
	__getRoutes: function(was, clusters, sights) {
		const wrap = document.createElement("div");

		const assoc = {};
		sights.forEach(sight => assoc[sight.sightId] = sight);

		clusters.forEach((cluster, i) => {
			cluster.id = i + 1;
			wrap.insertAdjacentHTML("beforeend", this.__itemRoute(was, cluster, assoc));
		});

		return wrap;
	},

	/**
	 *
	 * @param {int[]} was
	 * @param {{id: int, items: int[]}} cluster
	 * @param {API.Sight[]} sights
	 * @private
	 */
	__itemRoute: function(was, cluster, sights) {
		let list = cluster.items.map(sightId => {
			const sight = sights[sightId];
			return [sight.lat, sight.lng, sight.sightId, sight.title, sight.isVerified];
		});

		list = Sugar.String.escapeHTML(JSON.stringify(list)).replace(/"/igm, "&quot;");

		const html = [`<h3>Путь #${cluster.id} <span data-geo-data="${list}" class="button">Показать на карте</span></h3>`];

		cluster.items.forEach(sightId => {
			was.push(sightId);
			const sight = sights[sightId];

			html.push(NeuralPage.__itemSight(sight));
		});

		return html.join("");
	},

	__getList: function(was, items) {
		const list = document.createElement("div");

		list.insertAdjacentHTML("beforeend", "<h3>Просто интересные для Вас места</h3>");

		items.forEach(sight => {
			if (~was.indexOf(sight.sightId)) {
				return;
			}
			list.insertAdjacentHTML("beforeend", this.__itemSight(sight));
		});

		return list;
	},

	/**
	 *
	 * @param {API.Sight} sight
	 * @private
	 */
	__itemSight: function(sight) {
		const photo = sight.photo
			? `<div class="place-item-image" style="background-image: url(${sight.photo.photo200});"></div>`
			: "";

		const city = sight.city
			? `<p>Город: <a href="/sight/search?cityId=${sight.city.cityId}">${sight.city.name}</a></p>`
			: "";
		// <p class="search-item-rating"><i class="material-icons"><?=($item->getRating() > 0 ? "thumb_up" : "thumb_down")?></i> <?=$item->getRating();?></p>

		const sightId = sight.sightId;
		return `<li class="search-item place-item ${this.__getClasses(sight)}">
	<div class="place-item-photo">${photo}</div>
	<div class="place-item-content">
		<h5><a href="/sight/${sightId}" target="_blank" class="snippet-break-words">${sight.title}</a></h5>
		<p>Индекс интереса: ${(sight.interest.value * 100).toFixed(1)}%</p>
		${city}
		<p class="snippet-break-words">${sight.description}</p>
	</div>
</li>`;
	},

	/**
	 *
	 * @param {API.Sight} sight
	 * @private
	 */
	__getClasses: function(sight) {
		const cls = [];
		if (sight.isVerified) {
			cls.push("search-item--verified");
		}

		if (sight.isArchived) {
			cls.push("search-item--archived");
		}

		if (sight.visitState === API.sights.visitState.VISITED) {
			cls.push("search-item--visited");
		}

		if (sight.visitState === API.sights.visitState.DESIRED) {
			cls.push("search-item--desired");
		}
		return cls.join(" ");
	},

	__initButtonOpenRouteMap: function() {
		Array.from(document.querySelectorAll("[data-geo-data]")).forEach(button => {
			button.addEventListener("click", event => {
				NeuralPage.showModalRoute(JSON.parse(button.dataset.geoData));
			});
		});
	},

	showModalRoute: function(route) {
		const aLat = route.map(info => info.lat);
		const aLng = route.map(info => info.lng);

		let latTop = Math.max.apply(null, aLat);
		let latBottom = Math.min.apply(null, aLat);
		let lngLeft = Math.min.apply(null, aLng);
		let lngRight = Math.max.apply(null, aLng);

		const latCenter = (latTop - latBottom) / 2;
		const lngCenter = (lngRight - lngLeft) / 2;

		showModalMap(latCenter, lngCenter, 14, {
			onReady: yMap => {
				const collection = new ymaps.GeoObjectCollection({
					children: route.map(info => {
						return new ymaps.Placemark([info[0], info[1]], {
							balloonContentHeader: info[3],
							balloonContentBody: info[2]
						}, {
							preset: "islands#blueDotIcon",
						})
					})
				});

				yMap.geoObjects.add(collection);

				yMap.setBounds(collection.getBounds());
			}
		})
	},

	openModalRouteSettings: function() {
		const modal = new Modal({
			title: "Задать параметры для построения маршрута",
			content: ce("div", {"class": "round-loader round-loader--v50 round-loader--center"})
		});

		const form = ce("form");

		modal.show();

		const go = () => {
			const val = shakeOutForm(form, true);
			this.init({force: true, args: val});

			modal.release();
		};

		/**
		 *
		 * @param {{
		 *     name: string,
		 *     label: string,
		 *     type: string,
		 *     variants: {name: string, label: string}[]=,
		 *     defaultValue: string|boolean=
		 * }[]} items
		 */
		const show = items => {
			items.forEach(item => {
				let node = null;
				let rnd = Math.floor(Math.random() * 10000);
				switch (item.type) {
					case "select":
						const options = item.variants.map(v => ce("option", {value: v.name}, null, v.label));
						node = ce("div", {"class": "fi-wrap"}, [
							ce("select", {name: item.name, id: rnd + "_" + item.name}, options),
							ce("label", {"for": rnd + "_" + item.name}, null, item.label)
						]);
						break;

					case "checkbox":
						node = ce("label", {"class": "fi-checkbox"}, [
							ce("input", {type: "checkbox", name: item.name, value: "1"}),
							ce("span", null, null, item.label)
						]);
						break;

				}

				if (node !== null) {
					form.appendChild(node);
				}
			});
			modal.setContent(form);
			modal.setFooter(ce("div", null, [
				ce("button", {onclick: modal.release.bind(modal)}, null, "Закрыть"),
				ce("button", {onclick: go}, null, "Построить"),
			]));
		};

		API.neuralNetwork.getParametersForRouting().then(res => {
			show(res.items);
		}).catch(e => console.error(e));
	}
};