window.Index = {

	go2PlaceById: function(event) {
		event.preventDefault();
		window.location.href = "/sight/" + this.pointId.value;
		return false;
	}

};

ge("__index-button-map").addEventListener("submit", Index.go2PlaceById);