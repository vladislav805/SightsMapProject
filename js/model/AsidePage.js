/**
 * Страница
 * @param {{
 *   pageTitle: string,
 *   pageContent: string|HTMLElement|Node,
 *   backTitle: string=,
 *   data: *=,
 *   onClose: function=
 * }} options
 * @constructor
 */
function AsidePage(options) {
	this.mOptions = Sugar.Object.defaults(options, {backTitle: "Назад", data: {}});
	this.init();
}

AsidePage.prototype = {

	/** @var {object} */
	mOptions: null,

	/** @var {HTMLElement} */
	mNodeWrap: null,

	/** @var {HTMLElement} */
	mNodeScroll: null,

	/** @var {HTMLElement} */
	mNodeContent: null,

	/** @var {object} */
	mArgs: null,

	/** @var {Aside} */
	mStack: null,

	/**
	 * Инициализация
	 * Создание элементов
	 */
	init: function() {
		this.mNodeScroll = ce("div", {"class": "page-scroll"}, [
			this.mNodeWrap = ce("div", {"class": "page-wrap"}, [
				this.getHeader(),
				this.mNodeContent = ce("div", {"class": "page-content"}, [this.mOptions.pageContent])
			])
		]);
	},

	/**
	 * Проверяет, открыта ли в данный момент эта страница
	 * @returns {boolean}
	 */
	isOpened: function() {
		return this.mNodeScroll.classList.contains(AsidePage.CLASS_NAME_OPENED);
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
			this.mNodeScroll.classList.add(AsidePage.CLASS_NAME_OPENED);
		}.bind(this));
	},

	/**
	 * Закрытие страницы
	 * @returns {Promise}
	 */
	__close: function() {
		var w = this.mNodeWrap;
		return new Promise(function(r) {
			var ok = function() {
				removeEvent.bind(this, "transitionend webkitTransitionEnd otransitionend", w, ok);
				r();
			}.bind(this);
			addEvent("transitionend webkitTransitionEnd otransitionend", w, ok);
			this.mNodeScroll.classList.remove(AsidePage.CLASS_NAME_OPENED);
		}.bind(this));
	},

	/**
	 * @returns {HTMLElement}
	 */
	getNode: function() {
		return this.mNodeScroll;
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
		var close = function() {
			isCurrentAsideOpenPointInfo() && this.mStack.pop();
		}.bind(this);
		return ce("div", {"class": "page-head", onclick: close}, [
			getIcon("e317"),
			this.mOptions.backTitle
		]);
	},

	notifyClose: function() {
		console.log('notify close');
		this.registerStack(null);
		this.mOptions.onClose && this.mOptions.onClose(this.mOptions.data);
	},

	registerStack: function(stack) {
		this.mStack = stack;
	}

};

AsidePage.CLASS_NAME_OPENED = "page-opened";