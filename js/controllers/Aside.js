var Aside = {

	/**
	 * @var {HTMLElement}
	 */
	mInfoNodeWrap: null,

	/**
	 * @var AsidePage[]
	 */
	mStack: [],

	/**
	 * Инициализация информационной плашки
	 */
	init: function() {
		this.mInfoNodeWrap = g("aside");
	},

	/**
	 * Добавление в стек страницы
	 * @param {AsidePage} page
	 * @returns {Aside}
	 */
	push: function(page) {
		this.mStack.push(page);
		page.registerStack(this);
		this.mInfoNodeWrap.appendChild(page.getNode());
		setTimeout(page.open.bind(page), 100);
		return this;
	},

	/**
	 * Удаление из стека страницы
	 * @returns {Aside}
	 */
	pop: function() {
		var asideItem = this.mStack.pop();
		asideItem.__close().then(function() {
			asideItem.notifyClose();
			this.mInfoNodeWrap.removeChild(asideItem.getNode());
		}.bind(this));
		return this;
	},

	/**
	 * Возвращает самую верхнюю показываемую страницу
	 * @returns {AsidePage}
	 */
	getLast: function() {
		return this.mStack[this.mStack.length - 1];
	},

};