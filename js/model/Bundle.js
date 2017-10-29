function Bundle(items) {
	this.mItems = items || {};
}

Bundle.prototype = {

	/**
	 * Заменяет значение на ключе id
	 * @param {int|string} id
	 * @param {*} obj
	 * @returns {Bundle}
	 */
	set: function(id, obj) {
		this.mItems[id] = obj;
		return this;
	},

	/**
	 * Вернуть значение по ключу
	 * @param {int|string} id
	 * @returns {*}
	 */
	get: function(id) {
		return this.mItems[id];
	},

	/**
	 * Вернуть все пары ключ/значение
	 * @returns {object}
	 */
	getAll: function() {
		return this.mItems;
	},

	/**
	 * Очистка
	 */
	clear: function() {
		this.mItems = {};
	},

	/**
	 * Проверяет наличие значение по ключу
	 * @param {int|string} id
	 * @returns {boolean}
	 */
	has: function(id) {
		return id in this.mItems;
	}

};