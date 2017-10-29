function Point(p) {
	this.ownerId = p.ownerId;
	this.pointId = p.pointId || 0;
	this.markIds = p.markIds || [];
	this.lat = p.lat;
	this.lng = p.lng;
	this.dateCreated = p.dateCreated ? new Date(p.dateCreated * 1000) : null;
	this.dateUpdated = p.dateUpdated ? new Date(p.dateUpdated * 1000) : null;
	this.title = p.title || "";
	this.description = p.description || "";
	this.isVerified = p.isVerified || false;
	this.visitState = p.visitState || Point.visitState.NOT_VISITED;

	this.canModify = p.canModify;

	this.mAuthor = p.author;
	this.mPlacemark = null;
	this.mListItem = null;
	this.mPhotos = [];
	console.log(this.markIds)
}

Point.prototype = {

	/**
	 * @returns {int}
	 */
	getId: function() {
		return this.pointId;
	},

	/**
	 * @returns {string}
	 */
	getTitle: function() {
		return this.title;
	},

	/**
	 * @returns {string}
	 */
	getDescription: function() {
		return this.description;
	},

	/**
	 *
	 * @returns {[float, float]}
	 */
	getCoordinates: function() {
		return [this.lat, this.lng];
	},

	/**
	 *
	 * @returns {float}
	 */
	getLat: function() {
		return this.lat;
	},

	/**
	 *
	 * @returns {float}
	 */
	getLng: function() {
		return this.lng;
	},

	/**
	 * @returns {ymaps.GeoObject}
	 */
	getGeoObject: function() {
		return this.getPlacemark().getGeoObject();
	},

	/**
	 * @returns {Placemark}
	 */
	getPlacemark: function() {
		if (!this.mPlacemark) {
			this.mPlacemark = new Placemark(this);
		}
		return this.mPlacemark;
	},

	/**
	 * @returns {PointListItem}
	 */
	getListItem: function() {
		if (!this.mListItem) {
			this.mListItem = new PointListItem(this);
		}
		return this.mListItem;
	},

	/**
	 * @returns {boolean}
	 */
	isExists: function() {
		return this.pointId > 0;
	},

	/**
	 * @returns {int[]}
	 */
	getMarkIds: function() {
		return this.markIds;
	},

	/**
	 * @returns {{ownerId: *, pointId: (Map.handle.point|{move, visit, link, edit, save, remove}|*), isVisited: *, title: *, description: *, isVerified: number, lat: *, lng: *}}
	 */
	getSingle: function() {
		return {ownerId: this.ownerId, pointId: this.point, isVisited: this.isVisited, title: this.title, description: this.description, isVerified: +this.isVerified, lat: this.lat, lng: this.lng};
	},

	getVisitState: function() {
		return this.visitState;
	},

	/**
	 * @returns {Point}
	 */
	notify: function() {
		this.getPlacemark().setNormalProperties();
		this.getListItem().update();
		return this;
	},

	/**
	 * @returns {string}
	 */
	getLink: function () {
		var params = {lat: this.lat.toFixed(6), lng: this.lng.toFixed(6), z: 15, id: this.pointId};

		return "http://" + window.location.hostname + "/?" + Sugar.Object.toQueryString(params);
	},

	/**
	 *
	 * @param {Photo[]} photos
	 * @returns {Point}
	 */
	setPhotos: function(photos) {
		this.mPhotos = photos;
		return this;
	},

	/**
	 *
	 * @returns {Photo[]}
	 */
	getPhotos: function() {
		return this.mPhotos;
	}
};

Point.visitState = {
	NOT_VISITED: 0,
	VISITED: 1,
	DESIRED: 2
};