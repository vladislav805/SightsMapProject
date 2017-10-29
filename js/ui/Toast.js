function Toast(text, options) {
	this.mNode = ce("div", {"class": "toast"}, [
		this.mNodeText = ce("div", {"class": "toast-text"}),
		this.mNodeButtonWrap = ce("div", {"class": "toast-buttons"})
	]);
	options = options || {};
	this.setText(text);
	this.setButtons(options.buttons);
	this.init();
}

Toast.prototype = {

	mInit: null,
	mNode: null,
	mNodeText: null,
	mNodeButtonWrap: null,

	mText: "",
	mButtons: [],

	/**
	 * Init
	 */
	init: function() {
		document.getElementsByTagName("body")[0].appendChild(this.mNode);
		this.mInit = Date.now();
	},

	/**
	 * Set content text
	 * @param {String} text
	 * @returns {Toast}
	 */
	setText: function(text) {
		this.mText = text;
		this.updateNode();
		return this;
	},

	/**
	 * Set array of buttons
	 * @param {HTMLInputElement[]} items
	 * @returns {Toast}
	 */
	setButtons: function(items) {
		items = items || [];
		this.mButtons = items;
		this.updateNode();
		return this;
	},

	/**
	 * Update DOM-elements by "virtual" info
	 */
	updateNode: function() {
		this.mNodeText.innerHTML = this.mText;
		this.updateButtons();
	},

	/**
	 * Updating buttons
	 */
	updateButtons: function() {
		this.mNodeButtonWrap.innerHTML = "";

		this.mButtons.map(function(button) {
			return ce("div", {
				"class": "toast-button",
				onclick: button.onclick
			}, null, button.label);
		}, this).forEach(function(item) {
			this.mNodeButtonWrap.appendChild(item);
		}, this);
	},

	/**
	 * Open toast
	 * @param {int} duration
	 * @returns {Toast}
	 */
	open: function(duration) {
		if (Date.now() - this.mInit < Toast.MIN_DELAY_OPEN) {
			setTimeout(this.open.bind(this, duration), Toast.MIN_DELAY_OPEN);
			return this;
		}

		duration = duration || 4000;
		if (this.mTimeoutOpen) {
			clearTimeout(this.mTimeoutOpen);
		}

		this.mNode.classList.add(Toast.CLASS_NAME_OPENED);
		this.mTimeoutOpen = setTimeout(this.close.bind(this), duration);
		return this;
	},

	/**
	 * Close toast
	 * @returns {Toast}
	 */
	close: function() {
		clearTimeout(this.mTimeoutOpen);
		this.mNode.classList.remove(Toast.CLASS_NAME_OPENED);
		return this;
	}
};

Toast.CLASS_NAME_OPENED = "toast-open";
Toast.MIN_DELAY_OPEN = 50;