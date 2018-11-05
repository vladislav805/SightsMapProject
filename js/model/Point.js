function Point(p) {
	this.populate(p);
	this.mAuthor = p.author;
	this.mPhotos = [];
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
	 * @returns {int[]}
	 */
	getMarkIds: function() {
		return this.markIds;
	},

	/**
	 * @returns {City|null}
	 */
	getCity: function() {
		return this.city;
	},

	/**
	 * @returns {int}
	 */
	getVisitState: function() {
		return this.visitState;
	},

	/**
	 * @returns {string}
	 */
	getLink: function() {
		var params = {id: this.pointId};

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

	populate: function(p) {
		this.ownerId = p.ownerId;
		this.pointId = p.pointId || 0;
		this.markIds = p.markIds || [];
		this.city = p.city ? new City(p.city) : null;
		this.lat = p.lat;
		this.lng = p.lng;
		this.dateCreated = p.dateCreated ? new Date(p.dateCreated * 1000) : null;
		this.dateUpdated = p.dateUpdated ? new Date(p.dateUpdated * 1000) : null;
		this.title = p.title || "";
		this.description = p.description || "";
		this.isVerified = p.isVerified || false;
		this.isArchived = p.isArchived || false;
		this.visitState = p.visitState || Point.visitState.NOT_VISITED;

		this.canModify = p.canModify;
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