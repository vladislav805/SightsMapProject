/**
 *
 * @param {HTMLElement|string} node
 * @constructor
 */
function Select(node) {
	if (typeof node === "string") {
		node = g(node);
	} else if (node instanceof HTMLElement) {
		this.mNode = node;
		this.initByNode();
	} else  {
		this.initSingle();
	}

	this.mItems = {};
	this.init();

	if (!node) {
		throw new TypeError("Error while init Select: node is not defined");
	}

}

Select.prototype = {

	/** @var {HTMLElement} */
	mNode: null,

	/** @var {HTMLElement} */
	mNodeLabel: null,

	/** @var {HTMLElement} */
	mNodeValue: null,

	/** @var {HTMLElement} */
	mNodeIcon: null,

	/** @var {HTMLElement} */
	mNodeItems: null,

	/** @var {object} */
	mItems: null,

	/**
	 *
	 */
	init: function() {

	},

	/**
	 *
	 */
	initByNode: function() {
		var t;
		if (t = this.mNode.querySelector(".x-select-label")) {
			this.mNodeLabel = t;
		}

		if (t = this.mNode.querySelector(".x-select-items")) {
			this.mNodeItems = t;
		}

		if (t = this.mNode.querySelector(".x-select-value")) {
			this.mNodeValue = t;
		}

		if (t = this.mNodeLabel.querySelector(".material-icons")) {
			this.mNodeIcon = t;
		}

		this.mNode.classList.add("x-select-wrap");
	},

	/**
	 *
	 */
	initChildren: function() {
		this.mNodeLabel = ce("div", {"class": "x-select-label"});
		this.mNodeItems = ce("div", {"class": "x-select-items"});
		this.mNodeValue = ce("div", {"class": "x-select-value"});
		this.mNode.appendChild(this.mNodeLabel);
		this.mNodeLabel.appendChild(this.mNodeValue);
		this.mNode.appendChild(this.mNodeItems);
	},

	/**
	 *
	 */
	initSingle: function() {
		this.initChildren();
		this.mNode = ce("div", {"class": "x-select-wrap"}, [this.mNodeLabel, this.mNodeItems]);
	},

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getNode: function() {
		return this.mNode;
	},

	/**
	 *
	 * @param {SelectItem|SelectItemCheckable} o
	 * @returns {Select}
	 */
	add: function(o) {
		this.mItems[o.getId()] = o;
		o.setParent(this);
		this.mNodeItems.appendChild(o.getNode());
		return this;
	},

	getNodeValue: function() {
		return this.mNodeValue;
	}

};