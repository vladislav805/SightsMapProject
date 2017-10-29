function User(u) {
	this.userId = u.userId || 0;
	this.login = u.login || "none";
	this.firstName = u.firstName;
	this.lastName = u.lastName;
	this.isOnline = u.isOnline || false;
	this.lastSeen = u .lastSeen;
	this.sex = u.sex;
	this.photo = u.photo ? new Photo(u.photo) : null;

	this.mNone = "__" in u;
}

User.prototype = {

	getId: function() {
		return this.userId;
	},

	getFullName: function() {
		return this.firstName + " " + this.lastName;
	},

	getFirstName: function() {
		return this.firstName;
	},

	getLogin: function() {
		return this.login;
	},

	getPhoto: function(size) {
		size = parseInt(size).range(0, 2);
		return this.photo.get(size);
	},

	isExists: function() {
		return this.mNone;
	}

};