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
	},

	/**
	 * Обновление данных
	 * @param {{lat, lng, ownerId, pointId, title, description, markIds, dateCreated, dateUpdated, visitState, isVerified}} point
	 * @returns {Place}
	 */
	update: function(point) {
		this.mInfo.populate(point);
		this.mItemList && this.mItemList.update();
		this.mPlacemark && this.mPlacemark.options.set("coordinates", this.mInfo.getCoordinates());
		return this;
	}
};

Place.mCache = new Bundle;

Place.get = function(point) {
	var pl;
	if (Place.mCache.has(point.pointId)) {
		pl = Place.mCache.get(point.pointId);
		pl.update(point);
	} else {
		pl = new Place(point);
		Place.mCache.set(pl.getId(), pl);
	}
	return pl;
};