function FilterMap() {
	this.markIds = [];
	this.onlyVerified = false;
	this.visitState = -1;
	this.query = "";
}

FilterMap.prototype = {

	/**
	 * Замена списка выбранных категорий
	 * @param {int[]|string} ids
	 * @returns {FilterMap}
	 */
	setMarkIds: function(ids) {
		this.markIds = Array.isArray(ids) ? ids : ids.split(",");
		return this;
	},

	/**
	 * Замена параметра "только проверенные"
	 * @param {boolean} state
	 * @returns {FilterMap}
	 */
	setOnlyVerified: function(state) {
		this.onlyVerified = state;
		return this;
	},

	/**
	 * Замена параметра "посещенность"
	 * @param {int} state
	 * @returns {FilterMap}
	 */
	setVisitState: function(state) {
		this.visitState = state;
		return this;
	},

	/**
	 * @returns {int[]}
	 */
	getMarkIds: function() {
		return this.markIds;
	},

	/**
	 * @returns {boolean}
	 */
	getOnlyVerified: function() {
		return this.onlyVerified;
	},

	/**
	 * @returns {int}
	 */
	getVisitState: function() {
		return this.visitState;
	},

	getQuery: function() {
		return {
			markIds: this.markIds.join(","),
			onlyVerified: +this.onlyVerified,
			visitState: this.visitState
		};
	}

};