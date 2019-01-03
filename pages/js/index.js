var Index = {

	init: function() {
		onReady(() => ge("__index-gotoById").addEventListener("submit", Index.go2PlaceById));
	},

	go2PlaceById: function(event) {
		event.preventDefault();
		navigateTo("/sight/" + this.sightId.value, null);
		return false;
	}

};