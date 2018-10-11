function City(p) {
	this.cityId = p.cityId;
	this.title = p.name;
}

City.prototype = {

	getId: function() {
		return this.cityId;
	},

	getTitle: function() {
		return this.title;
	}

};