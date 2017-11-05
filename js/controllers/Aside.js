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
		this.mInfoNodeWrap.appendChild(page.getNode());
		page.open();
		return this;
	},

	/**
	 * Удаление из стека страницы
	 * @returns {Aside}
	 */
	pop: function() {
		var asideItem = this.mStack.pop();
		asideItem.close().then(function() {
			this.mInfoNodeWrap.removeChild(asideItem.getNode());
		});
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