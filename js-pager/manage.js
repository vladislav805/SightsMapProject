window.addEventListener("DOMContentLoaded", function() {
	if (document.querySelector("#manage-map")) {
		ymaps.ready(function() {
			ManageMap.init();
		});

		var dropArea = document.getElementById("__photo-drop-zone");

		["dragenter", "dragover", "dragleave", "drop"].forEach(function(eventName) {
			dropArea.addEventListener(eventName, function (e) {
				e.preventDefault();
				e.stopPropagation();
			}, false);
		});


		["dragenter", "dragover"].forEach(function(eventName) {
			dropArea.addEventListener(eventName, highlight, false);
		});

		["dragleave", "drop"].forEach(function(eventName) {
			dropArea.addEventListener(eventName, unhighlight, false);
		});

		function highlight(e) {
			dropArea.style.borderColor = "red";
			dropArea.classList.add("highlight");
		}

		function unhighlight(e) {
			dropArea.style.borderColor = "var(--primaryBackgroundColor)";
			dropArea.classList.remove("highlight");
		}

		dropArea.addEventListener('drop', function(e) {
			var dt = e.dataTransfer;
			var files = dt.files;

			ManageMap.handleFiles(files);
		}, false);
	}
});

var ManageMap = {

	mMap: null,

	mPlacemark: null,

	init: function() {
		this.mMap = new ymaps.Map(document.querySelector("#manage-map"), {
			center: [0, 0],
			zoom: 2,
			controls: []
		});

		this.mMap.events.add("click", function(event) {
			if (this.mMap.balloon.isOpen()) {
				this.mMap.balloon.close();
			}
		}.bind(this));

		ymaps.geolocation.get({
			provider: "auto",
			mapStateAutoApply: true
		}).then(function(result) {
			var c = result.geoObjects.position;
			this.mMap.setCenter(c, 11);
		}.bind(this));
	},

	handleFiles: function(files) {
		console.log(files);
	}

};