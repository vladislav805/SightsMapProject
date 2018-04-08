window.addEventListener("DOMContentLoaded", function() {
	if (document.querySelector("#manage-map")) {
		ymaps.ready(function() {
			ManageMap.init();
		});
	}
});

var ManageMap = {

	mMap: null,

	init: function() {
		this.mMap = new ymaps.Map(document.querySelector("#manage-map"), {
			center: [0, 0],
			zoom: 2,
			controls: []
		});
	}

};