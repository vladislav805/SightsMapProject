/**
 *
 * @param {string} title
 * @param {*} id
 * @param {function=} onClick
 * @constructor
 */
function SelectItem(title, id, onClick) {
	this.mNode = ce("div", {"class": "x-select-item"}, null, title);
	this.mNode.addEventListener("click", this.fireClick.bind(this));
	this.mId = id;
	this.setOnClick(onClick);
}

SelectItem.prototype = {

	getData: function() {
		return this.mId;
	},

	setOnClick: function(fx) {
		this.mOnClick = fx;
		return this;
	},

	fireClick: function() {
		this.mOnClick && this.mOnClick({item: this.mNode, id: this.mId, instance: this});
	},

	getNode: function() {
		return this.mNode;
	}

};