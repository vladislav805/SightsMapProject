window.Index = {

	randomPlace: function() {
		API.points.getRandomPlace().then(function(res) {
			// TODO: ajax
			window.location.href = "/place/" + res.pointId;
		});
	},

	go2PlaceById: function(event) {
		event.preventDefault();
		window.location.href = "/place/" + this.pointId.value;
		return false;
	}

};

ge("__index-button-random").addEventListener("click", Index.randomPlace);
ge("__index-button-map").addEventListener("submit", Index.go2PlaceById);