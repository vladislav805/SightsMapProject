window.addEventListener("DOMContentLoaded", function() {
	ymaps.ready(function() {
		new BaseMap(ge("manage-map"), null, {
			updateAddressOnChange: false,

			/**
			 * @param {ymaps.Map} yMap
			 */
			onMapReady: function(yMap) {

			}
		});
	});
});