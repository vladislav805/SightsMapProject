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
	mNodeItems: null,

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

		this.mNode.classList.add("x-select-wrap");
	},

	/**
	 *
	 */
	initChildren: function() {
		this.mNodeLabel = ce("div", {"class": "x-select-label"});
		this.mNodeItems = ce("div", {"class": "x-select-items"});
		this.mNode.appendChild(this.mNodeLabel);
		this.mNode.appendChild(this.mNodeItems);
	},

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
		this.mNodeItems.appendChild(o.getNode());
		return this;
	}

};