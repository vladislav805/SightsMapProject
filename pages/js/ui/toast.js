"use strict";

/**
 *
 * @param {string} text
 * @param {{onClick: function, label: string}=} action
 * @param {int=} duration
 * @class
 */
function Toast(text, action, duration) {

	this.nText = ce("div", {"class": "toast-text"});
	this.nAction = ce("div", {"class": "toast-action", onclick: () => {
		this.mOnActionClick && this.mOnActionClick.call(this);
	}});

	this.root = ce("div", {"class": "toast"}, [this.nText, this.nAction]);

	this.setText(text);

	action && this.setAction(action);

	this.__insert();
}

Toast.prototype = {
	TOAST__ACTIVE: "toast--active",
	TOAST_DEFAULT_DURATION: 2000,

	mOnActionClick: null,
	mTimer: null,

	/**
	 * @param {int} duration
	 * @returns {Toast}
	 */
	show: function(duration) {
		this.__clearTimer();
		this.root.classList.add(this.TOAST__ACTIVE);
		this.mTimer = setTimeout(this.hide.bind(this), duration);
		return this;
	},

	/**
	 * @returns {Toast}
	 */
	hide: function() {
		this.__clearTimer();
		this.root.classList.remove(this.TOAST__ACTIVE);
		return this;
	},

	__clearTimer: function() {
		if (this.mTimer) {
			clearTimeout(this.mTimer);
			this.mTimer = null;
		}
	},

	/**
	 * @param {string} text
	 * @returns {Toast}
	 */
	setText: function(text) {
		this.nText.innerText = text;
		return this;
	},

	/**
	 * @param {{onClick: function, label: string}} action
	 * @returns {Toast}
	 */
	setAction: function(action) {
		this.mOnActionClick = action.onClick;
		this.nAction.innerText = action.label;
		return this;
	},

	__insert: function() {
		getBody().appendChild(this.root);
	}
};

