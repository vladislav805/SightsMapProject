const NeuralPage = {

	init: function() {
		API.neuralNetwork.getInterestedSights({}).then(NeuralPage.onResponse.bind(this)).catch(NeuralPage.onError);
	},

	onError: function(error) {
		console.error(error);
	},

	/**
	 *
	 * @param {{count: int, error: int, items: API.Sight[], clusters: {id: int, items: int[]}[]}} data
	 */
	onResponse: function(data) {
		const was = [];

		const wrap = ge("neural_list");
		wrap.classList.add("neural-list-loaded");
		wrap.appendChild(NeuralPage.__getRoutes(was, data.clusters, data.items));
		wrap.appendChild(NeuralPage.__getList(was, data.items));
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
	__itemRoute: function(was, cluster, sights, i) {
		const html = [`<h3>Путь #${cluster.id}</h3>`];

		cluster.items.forEach(sightId => {
			was.push(sightId);
			const sight = sights[sightId];

			html.push(NeuralPage.__itemSight(sight));
			/*html.push(`<div class="search-item place-item ${this.__getClasses(sight)}">
	<div class="place-item-photo">${sight.photo}</div>
	<div class="place-item-content">
		<h5><a href="/sight/${sightId}" target="_blank" class="snippet-break-words">${cluster.title}</a></h5>
		<p>Индекс интереса: ${cluster.interest.value.toFixed(2)}%</p>
		${city}
		<p class="snippet-break-words">${cluster.description}</p>
	</div>
</div>`);*/
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
		<p>Индекс интереса: ${sight.interest.value.toFixed(2)}%</p>
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

		if (sight.visitState === API.points.visitState.VISITED) {
			cls.push("search-item--visited");
		}

		if (sight.visitState === API.points.visitState.DESIRED) {
			cls.push("search-item--desired");
		}
		return cls.join(" ");
	}
};