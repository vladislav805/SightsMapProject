function Place(o) {
	this.mInfo = new Point(o);
	this.mItemList = null;
	this.mPlacemark = null;
}

Place.prototype = {

	/**
	 * @var {Point}
	 */
	mInfo: null,

	/**
	 * @var {PointListItem}
	 */
	mItemList: null,

	/**
	 * @var {ymaps.GeoObject}
	 */
	mPlacemark: null,

	/**
	 *
	 * @returns {int}
	 */
	getId: function() {
		return this.mInfo.getId();
	},

	/**
	 *
	 * @returns {Point}
	 */
	getInfo: function() {
		return this.mInfo;
	},

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getListItemNode: function() {
		return (this.mItemList ? this.mItemList : (this.mItemList = new PointListItem(this))).getNode();
	},

	/**
	 *
	 * @returns {ymaps.GeoObject}
	 */
	getPlacemark: function() {
		if (!this.mPlacemark) {
			console.log("CREATED NEW");
			this.mPlacemark = new ymaps.GeoObject({
				geometry: {
					type: "Point",
					coordinates: this.mInfo.getCoordinates()
				}
			}, {
				preset: "islands#icon"
			});

			this.mPlacemark.events.add("click", Main.fire.bind(Main, EventCode.POINT_CLICK, {point: this.mInfo}));
		}

		return this.mPlacemark;
	}
};