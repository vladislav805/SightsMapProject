function Comment(c) {
	this.commentId = c.commentId;
	this.date = new Date(c.date * 1000);
	this.userId = c.userId;
	this.text = c.text;
	this.canModify = c.canModify;

	this.author = c.author;
}

Comment.prototype = {

	/**
	 * @returns {int}
	 */
	getId: function() {
		return this.commentId;
	},

	/**
	 * @returns {Date}
	 */
	getDate: function() {
		return this.date;
	},

	/**
	 * @returns {string}
	 */
	getText: function() {
		return this.text;
	},

	/**
	 * @returns {boolean}
	 */
	getCanModify: function() {
		return this.canModify;
	}

};