function Mark(p) {
	this.markId = p.markId;
	this.title = p.title;
	this.color = +p.color;
}

Mark.prototype = {

	getId: function() {
		return this.markId;
	},

	getTitle: function() {
		return this.title;
	},

	getColor: function() {
		return this.color;
	}

};