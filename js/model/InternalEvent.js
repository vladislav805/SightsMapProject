function InternalEvent(d) {
	this.eventId = d.eventId;
	this.date = new Date(d.date * 1000);
	this._isNew = d.isNew;
	this.type = d.type;
	this.actionUserId = d.actionUserId;
	this.ownerUserId = d.ownerUserId;
	this.subjectId = d.subjectId;
}

InternalEvent.prototype = {

	/**
	 * @returns {int}
	 */
	getId: function() {
		return this.eventId;
	},

	/**
	 * @returns {int}
	 */
	getType: function() {
		return this.type;
	},

	/**
	 * @returns {int}
	 */
	isNew: function() {
		return this._isNew;
	},

	/**
	 * @returns {Date}
	 */
	getDate: function() {
		return this.date;
	},

	/**
	 * @returns {int}
	 */
	getActionUserId: function() {
		return this.actionUserId;
	},

	/**
	 * @returns {int}
	 */
	getOwnerUserId: function() {
		return this.ownerUserId;
	},

	/**
	 * @returns {int}
	 */
	getSubjectId: function() {
		return this.subjectId;
	}

};