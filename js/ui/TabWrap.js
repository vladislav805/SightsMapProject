function TabWrap(items) {

	this.init();
	this.mItems = [];
	this.mTabs = {};
	if (items) {
		items.forEach(this.add.bind(this));
		this.commit();
	}
}

TabWrap.prototype = {

	/** @var {HTMLElement} */
	mNode: null,

	/** @var {HTMLElement} */
	mNodeTabs: null,

	/** @var {HTMLElement} */
	mNodeContent: null,

	/** @var {Tab[]} */
	mItems: null,

	/** @var {Tab{}} */
	mTabs: null,

	/** @var int */
	mSelected: -1,

	/** @var {Function|null} */
	mOnChange: null,

	/**
	 * Init
	 */
	init: function() {
		this.mNodeTabs = ce("div", {"class": "x-tab-items"});
		this.mNodeContent = ce("div", {"class": "x-tab-contents"});
		this.mNode = ce("div", {"class": "x-tab-wrap"}, [this.mNodeTabs, this.mNodeContent]);
	},

	/**
	 * Add tab to list
	 * @param {Tab} tab
	 * @returns {TabWrap}
	 */
	add: function(tab) {
		this.mItems.splice(this.mItems.length, 0, tab);
		tab.setParent(this);
		if (this.mItems.length === 1) {
			this.setSelected(tab);
		}
		return this;
	},

	/**
	 * Remove tab from list
	 * @param {Tab} tab
	 * @returns {TabWrap}
	 */
	remove: function(tab) {
		var index = this.mItems.indexOf(tab);
		if (~index) {
			this.mItems.splice(index, 1);
		}

		if (index === this.mSelected) {
			this.mSelected = Math.max(0, Math.min(this.mItems.length - 1, index - 1));
		}

		this.mTabs[tab.getName()] = null;
		return this;
	},

	/**
	 * Returns current index of selected tab
	 * @returns {int}
	 */
	getSelectedIndex: function() {
		return this.mSelected;
	},

	/**
	 * Return tab by index in list
	 * @param {int} index
	 * @returns {Tab}
	 */
	get: function(index) {
		return this.mItems[index /*this.getSelectedIndex()*/ ];
	},

	/**
	 * Change listener for change
	 * @param {function} fx
	 * @returns {TabWrap}
	 */
	onChange: function(fx) {
		this.mOnChange = fx;
		return this;
	},

	/**
	 * Set tab as selected
	 * @param {Tab|int} tab
	 * @returns {TabWrap}
	 */
	setSelected: function(tab) {
		var newIndex = !(tab instanceof Tab) ? tab : this.mItems.indexOf(tab), tmp;
		tab = tab instanceof Tab ? tab : this.get(tab);

		if (newIndex !== this.mSelected) {
			tmp = this.get(this.mSelected);
			tmp && tmp.setActive(false);
			tab.setActive(true);
			this.setContent(tab);
			this.mOnChange && this.mOnChange({now: tab, previous: tmp});
		}

		this.mSelected = newIndex;
		return this;
	},

	/**
	 *
	 * @param {Tab} tab
	 * @returns {TabWrap}
	 */
	setContent: function(tab) {
		this.mNodeContent.innerHTML = "";
		this.mNodeContent.appendChild(tab.getContent());
		return this;
	},

	/**
	 *
	 */
	commit: function() {
		this.mNodeTabs.innerHTML = "";
		this.mItems.forEach(function(tab) {
			if (!(tab.getName() in this.mTabs)) {
				this.mNodeTabs.appendChild(this.mTabs[tab.getName()] = tab.getTab());
			}
		}.bind(this));
	},

	getNode: function() {
		return this.mNode;
	}

};