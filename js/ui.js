/**
 *
 * @param {HTMLElement} obj
 * @constructor
 */
function SelectCheckable(obj) {
	this.mSelect = new Select(obj);
	this.mItems = [];
	this.init();
}

SelectCheckable.CLASS_NAME = "x-select-checkable";

SelectCheckable.prototype = {

	mSelect: null,
	mItems: null,

	init: function() {
		this.mSelect.getNode().classList.add(SelectCheckable.CLASS_NAME);
	},

	getNode: function() {
		return this.mSelect.getNode();
	},

	add: function(label, id, checked) {
		var d = new SelectItemCheckable(label, id, this.toggleCheck.bind(this), checked);
		this.mItems.push(d);
		this.mSelect.add(d);
		return this;
	},

	toggleCheck: function(event) {
		event.instance.toggleState();
		event.instance.updateState();
		this.mOnChecked && this.mOnChecked.call(this, event);
	},

	setOnChecked: function(fx) {
		this.mOnChecked = fx;
		return this;
	},

	getSelected: function() {
		return this.mItems.filter(function(item) {
			return item.getState();
		}).map(function(item) {
			return item.getId();
		});
	}
};

function SelectItemCheckable(title, id, onClick, checked) {
	this.mNode = ce("div", {"class": "x-select-item"}, null, title);
	this.mNode.addEventListener("click", this.fireClick.bind(this));
	this.mId = id;
	this.mIsChecked = checked;
	this.mMain = null;
	this.setOnClick(onClick);
	this.updateState();
}

SelectItemCheckable.CLASS_NAME_ACTIVE = "x-select-checked";

SelectItemCheckable.prototype = {

	mIsChecked: false,

	updateState: function() {
		this.getNode().classList[this.mIsChecked ? "add" : "remove"](SelectItemCheckable.CLASS_NAME_ACTIVE);
	},

	getId: function() {
		return this.mId;
	},

	setOnClick: function(fx) {
		this.mOnClick = fx;
		return this;
	},

	getState: function() {
		return this.mIsChecked;
	},

	fireClick: function() {
		this.mOnClick && this.mOnClick({item: this.mNode, id: this.mId, instance: this});
	},

	toggleState: function() {
		this.mIsChecked = !this.mIsChecked;
	},

	getNode: function() {
		return this.mNode;
	},

	setParent: function(main) {
		this.mMain = main;
		return this;
	},

	getParent: function() {
		return this.mMain;
	}

};




function getLoader() {
	return ce("div", {"class": "loader-wrap"}, [ce("div", {"class": "loader"})]);
}

/**
 * Создает DOM-элемент для иконки из Material Fonts
 * @param {string} code
 * @param {string=} color
 * @returns {Node|HTMLElement}
 */
function getIcon(code, color) {
	return ce("i", {"class": "material-icons icon-" + (color || "gray")}, null, "&#x" + code + ";");
}

/**
 * Create field
 * @param {int} type
 * @param {string} name
 * @param {string} label
 * @param {string} value
 * @param {{checked: boolean=}=} options
 * @returns {Node}
 */
function getField(type, name, label, value, options) {
	var nodeLabel = ce("label", {"for": name}, null, label),
		nodeField, isReverse = false;
	options = options || {};
	switch (type) {
		case FIELD_TYPE_TEXT_SINGLE:
		case FIELD_TYPE_PASSWORD:
			nodeField = ce("input", {type: FIELD_TYPE_TEXT_SINGLE === type ? "text" : "password", name: name, id: "xf" + name, value: value });
			break;

		case FIELD_TYPE_TEXT:
			nodeField = ce("textarea", {name: name, id: "xf" + name}, null, value);
			nodeField.value = value;
			break;

		case FIELD_TYPE_CHECKBOX:
			nodeField = ce("input", {type: "checkbox", name: name});
			nodeField.checked = options.checked;
			nodeField.value = value;
			//nodeField = ce("div", {"class": "x-form-checkbox"}, [nodeField, ce("div", {"class": "x-form-checkbox-label"}, null, label)]);
			isReverse = true;
			break;

		case FIELD_TYPE_RADIO:
			nodeField = ce("input", {type: "radio", name: name});
			nodeField.checked = options.checked;
			nodeField.value = value;
			//nodeField = ce("div", {"class": "x-form-checkbox"}, [nodeField, ce("div", {"class": "x-form-checkbox-label"}, null, label)]);
			isReverse = true;
			break;

		default:
			nodeField = ce("div");
	}

	var child = [nodeLabel, nodeField];
	isReverse && (child = child.reverse());
	return ce("div", {"class": "x-form-row"}, child);
}

var FIELD_TYPE_TEXT_SINGLE = 1,
	FIELD_TYPE_TEXT = 2,
	FIELD_TYPE_CHECKBOX = 3,
	FIELD_TYPE_PASSWORD = 4,
	FIELD_TYPE_RADIO = 5;