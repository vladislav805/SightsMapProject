var Info = {

	CLASS_NAME_OPENED: "info-opened",

	/**
	 * @var {HTMLElement}
	 */
	mInfoNodeWrap: null,

	/**
	 * @var {HTMLElement}
	 */
	mInfoNodeContent: null,

	/**
	 * @var {object}
	 */
	mInfoArgs: null,

	/**
	 * Инициализация информационной плашки
	 */
	init: function() {
		this.mInfoNodeWrap = g("info");
		this.mInfoNodeContent = ce("div", {"class": "info-content-wrap"});

		this.mInfoNodeWrap.appendChild(this.getHeader());

		this.mInfoNodeWrap.appendChild(this.mInfoNodeContent);
	},

	/**
	 * Генерация шапки ддля закрытия плашки и возврата в список
	 * @returns {Node|HTMLElement}
	 */
	getHeader: function() {
		return ce("div", {"class": "info-head-wrap", onclick: this.close.bind(this)}, [
			getIcon("e317"),
			"Вернуться к списку"
		]);
	},

	/**
	 * Открытие плашки
	 * @returns {Info}
	 */
	open: function() {
		this.mInfoNodeWrap.classList.add(Info.CLASS_NAME_OPENED);
		return this;
	},

	/**
	 * Закрытие плашки
	 * @returns {Info}
	 */
	close: function() {
		this.mInfoNodeWrap.classList.remove(Info.CLASS_NAME_OPENED);
		this.mInfoArgs = null;
		return this;
	},

	/**
	 * Очистка содержимого плашки
	 * @returns {Info}
	 */
	clearContent: function() {
		var i = this.mInfoNodeContent.firstChild;
		while (i) {
			this.mInfoNodeContent.removeChild(i);
			i = this.mInfoNodeContent.firstChild;
		}
		return this;
	},

	/**
	 * Замена содержимого плашки
	 * @param {{node: HTMLElement|Node, args: object=}} page
	 * @returns {Info}
	 */
	setContent: function(page) {
		this.clearContent();
		this.mInfoArgs = page.args;
		this.mInfoNodeContent.appendChild(page.node);
		return this;
	},

	getArgs: function(key) {
		return this.mInfoArgs && this.mInfoArgs[key];
	}

};