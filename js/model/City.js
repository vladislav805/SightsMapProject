function City(p) {
	this.cityId = p.cityId;
	this.name = p.name;
}

City.prototype = {

	getId: function() {
		return this.cityId;
	},

	getTitle: function() {
		return this.name;
	}

};