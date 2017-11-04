function User(u) {
	this.userId = u.userId || 0;
	this.update(u);

	this.photo = u.photo ? new Photo(u.photo) : null;
	this.mNone = "__" in u;
}

User.prototype = {

	/**
	 *
	 * @returns {int}
	 */
	getId: function() {
		return this.userId;
	},

	/**
	 *
	 * @returns {string}
	 */
	getFullName: function() {
		return this.firstName + " " + this.lastName;
	},

	/**
	 *
	 * @returns {string}
	 */
	getFirstName: function() {
		return this.firstName;
	},

	/**
	 *
	 * @returns {string}
	 */
	getLogin: function() {
		return this.login;
	},

	/**
	 * Возвращает объект фотографии пользователя
	 * @returns {Photo}
	 */
	getPhoto: function() {
		return this.photo;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	isExists: function() {
		return this.mNone;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	getOnline: function() {
		return this.isOnline;
	},

	/**
	 *
	 * @returns {Date}
	 */
	getLastSeen: function() {
		return this.lastSeen;
	},

	/**
	 *
	 * @param {{login, firstName, lastName, isOnline, lastSeen, sex, photo}} u
	 */
	update: function(u) {
		this.login = u.login || "none";
		this.firstName = u.firstName;
		this.lastName = u.lastName;
		this.isOnline = u.isOnline || false;
		this.lastSeen = new Date(u.lastSeen * 1000);
		this.sex = u.sex;

	}

};

User.sCache = new Bundle;

User.get = function(user) {
	var usr;
	if (User.sCache.has(user.userId)) {
		usr = User.sCache.get(user.userId);
		usr.update(user);
	} else {
		usr = new User(user);
		User.sCache.set(usr.getId(), usr);
	}
	return usr;
};