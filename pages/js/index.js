const Index = {

	go2PlaceById: function(event) {
		event.preventDefault();
		navigateTo("/sight/" + this.sightId.value, null);
		return false;
	}

};

onReady(() => ge("__index-gotoById").addEventListener("submit", Index.go2PlaceById));