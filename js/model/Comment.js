function Comment(c) {
	this.commentId = c.commentId;
	this.date = new Date(c.date * 1000);
	this.userId = c.userId;
	this.text = c.text;
	this.canModify = c.canModify;

	this.author = c.author;
}

Comment.prototype = {

	getId: function() {
		return this.commentId;
	},

	getDate: function() {
		return this.date;
	}

};