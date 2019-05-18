const NeuralPage = {

	init: function() {
		API.neuralNetwork.getInterestedSights({}).then(NeuralPage.onResponse.bind(this)).catch(NeuralPage.onError);
	},

	onError: function(error) {
		console.error(error);
	},

	/**
	 *
	 * @param {{count: int, error: int, items: Sight[]}} data
	 */
	onResponse: function(data) {
		const items = data.items;

		const list = document.createElement("div");

		items.forEach(sight => {
			list.insertAdjacentHTML("beforeend", this.__itemSight(sight));
		});

		const wrap = ge("neural_list");
		wrap.classList.add("neural-list-loaded");
		wrap.appendChild(list);
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