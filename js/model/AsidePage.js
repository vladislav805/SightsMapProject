/**
 * Страница
 * @param {{
 *   pageTitle: string,
 *   pageContent: string|HTMLElement|Node,
 *   backTitle: string=,
 *   data: *=
 * }} options
 * @constructor
 */
function AsidePage(options) {
	this.mOptions = Sugar.Object.merge(options, {pageTitle: "Назад", data: {}});
	this.init();
}

AsidePage.prototype = {

	/** @var {object} */
	mOptions: {},

	/** @var {HTMLElement} */
	mNodeWrap: null,

	/** @var {HTMLElement} */
	mNodeContent: null,

	/** @var {object} */
	mArgs: null,

	/**
	 * Инициализация
	 * Создание элементов
	 */
	init: function() {
		console.log(this.mOptions)
		this.mNodeWrap = ce("div", {"class": "info-page-wrap"}, [
			this.getHeader(),
			this.mNodeContent = ce("div", {"class": "info-page-content"}, [this.mOptions.pageContent])
		]);
	},

	/**
	 * Проверяет, открыта ли в данный момент эта страница
	 * @returns {boolean}
	 */
	isOpened: function() {
		return this.mNodeWrap.classList.contains(AsidePage.CLASS_NAME_OPENED);
	},

	/**
	 * Открытие страницы
	 * @returns {Promise}
	 */
	open: function() {
		var w = this.mNodeWrap;
		return new Promise(function(r) {
			var ok = function() {
				removeEvent.bind(this, "transitionend webkitTransitionEnd otransitionend", w, ok);
				r();
			}.bind(this);
			addEvent("transitionend webkitTransitionEnd otransitionend", w, ok);
			this.mNodeWrap.classList.add(AsidePage.CLASS_NAME_OPENED);
		}.bind(this));
	},

	/**
	 * Закрытие страницы
	 * @returns {Promise}
	 */
	close: function() {
		var w = this.mNodeWrap;
		return new Promise(function(r) {
			var ok = function() {
				removeEvent.bind(this, "transitionend webkitTransitionEnd otransitionend", w, ok);
				r();
			}.bind(this);
			addEvent("transitionend webkitTransitionEnd otransitionend", w, ok);
			this.mNodeWrap.classList.remove(AsidePage.CLASS_NAME_OPENED);
		}.bind(this));
	},

	/**
	 * @returns {HTMLElement}
	 */
	getNode: function() {
		return this.mNodeWrap;
	},

	/**
	 * @returns {HTMLElement}
	 */
	getContentNode: function() {
		return this.mNodeContent;
	},

	/**
	 * @returns {*}
	 */
	getData: function() {
		return this.mOptions.data;
	},

	/**
	 * Генерация шапки ддля закрытия плашки и возврата в список
	 * @returns {Node|HTMLElement}
	 */
	getHeader: function() {
		return ce("div", {"class": "info-head-wrap", onclick: this.close.bind(this)}, [
			getIcon("e317"),
			this.mOptions.title
		]);
	},

};

AsidePage.CLASS_NAME_OPENED = "info-opened";