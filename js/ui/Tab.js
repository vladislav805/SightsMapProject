function Tab(options) {
	options = options || {};
	this.mName = options.name;
	this.init();
	this.setTitle(options.title);
	options.content && this.setContent(options.content);
}

Tab.prototype = {

	mName: null,
	mState: false,

	mParent: null,

	mTab: null,
	mContent: null,

	mOnOpen: null,
	mOnClose: null,

	onOpen: function(fx) {
		this.mOnOpen = fx;
		return this;
	},

	onClose: function(fx) {
		this.mOnClose = fx;
		return this;
	},

	init: function() {
		this.mTab = ce("div", {"class": "x-tab-item"});
		this.mContent = ce("div", {"class": "x-tab-content"});
		this.initEvents();
	},

	initEvents: function() {
		this.mTab.addEventListener("click", function() {
			this.mParent.setSelected(this);
		}.bind(this));
	},

	getName: function() {
		return this.mName;
	},

	getTab: function() {
		return this.mTab;
	},

	getContent: function() {
		return this.mContent;
	},

	setParent: function(parent) {
		this.mParent = parent;
		return this;
	},

	setTitle: function(title) {
		this.getTab().textContent = title;
	},

	setActive: function(state) {
		if (state !== this.mState) {
			this.mState = state;
			this.mTab.classList[state ? "add" : "remove"](Tab.CLASS_NAME_ACTIVE);
			var callback = this[state ? "mOnOpen" : "mOnClose"];
			callback && callback();
		}
		return this;
	},

	setContent: function(content) {
		if (content instanceof HTMLElement) {
			this.mContent.innerHTML = "";
			this.mContent.appendChild(content);
		} else {
			this.mContent.innerHTML = content;
		}
		return this;
	}
};

Tab.CLASS_NAME_ACTIVE = "x-tab-active";