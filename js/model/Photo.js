function Photo(p) {
	this.ownerId = p.ownerId;
	this.photoId = p.photoId;
	this.photo200 = p.photo200;
	this.photoMax = p.photoMax;
	this.date = new Date(p.date * 1000);
	this.type = p.type;
}

Photo.prototype = {

	getId: function() {
		return this.photoId;
	},

	getDate: function() {
		return this.date;
	},

	get: function(size) {
		return [this.photo200, this.photoMax][size];
	},

	getType: function() {
		return this.type;
	}

};

Photo.size = { THUMBNAIL: 0, ORIGINAL: 1 };
